<?php

namespace Kirby\Retour;

use Kirby\Cms\App;
use Kirby\Data\Data;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Yaml\Exception\ParseException;

#[CoversClass(Config::class)]
class ConfigTest extends TestCase
{
	protected const string VALID = "redirects:\n  - from: foo\n    to: bar\n";

	protected function config(string $file, array $options = []): Config
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $file,
				...$options
			]
		]);

		return Retour::instance($app)->config();
	}

	protected function entry(string $file): array|null
	{
		return Retour::instance()->kirby()->cache('distantnative.retour')->get('config-' . md5($file));
	}

	public function testCache(): void
	{
		F::write($file = $this->tmp . '/retour.yml', static::VALID);
		$config = $this->config($file);
		$entry  = $this->entry($file);

		$this->assertSame(hash('xxh128', static::VALID), $entry['hash']);
		$this->assertSame($config->get('redirects'), $entry['data']['redirects']);

		// unchanged contents are not parsed again
		$cache = Retour::instance()->kirby()->cache('distantnative.retour');
		$cache->set('config-' . md5($file), [...$entry, 'data' => ['cached' => true]]);
		$this->assertSame(['cached' => true], $config->read());
	}

	public function testCacheDisabled(): void
	{
		F::write($file = $this->tmp . '/retour.yml', static::VALID);
		$config = $this->config($file, ['distantnative.retour.cache' => false]);

		$this->assertSame('bar', $config->get('redirects')[0]['to']);
		$this->assertNull($this->entry($file));
	}

	public function testCacheExternalEdit(): void
	{
		F::write($file = $this->tmp . '/retour.yml', static::VALID);
		$config = $this->config($file);
		$mtime  = filemtime($file);

		// same size and modification time, but different contents
		F::write($file, str_replace('bar', 'baz', static::VALID));
		touch($file, $mtime);

		$this->assertSame('baz', $config->read()['redirects'][0]['to']);
	}

	public function testCacheInvalidFile(): void
	{
		F::write($file = $this->tmp . '/retour.yml', static::VALID);
		$config = $this->config($file);

		// failed reads are not cached
		F::write($file, "redirects:\n  - from: foo\n    to: [bar\n");

		for ($i = 0; $i < 2; $i++) {
			$this->assertSame([], $config->read());
			$this->assertInstanceOf(LogicException::class, $config->error());
		}

		$this->assertSame(hash('xxh128', static::VALID), $this->entry($file)['hash']);

		F::write($file, static::VALID);
		$this->assertSame('bar', $config->read()['redirects'][0]['to']);
		$this->assertNull($config->error());
	}

	public function testCacheWrite(): void
	{
		F::write($file = $this->tmp . '/retour.yml', static::VALID);
		$config = $this->config($file);
		$config->update(['ignore' => ['foo']]);

		$data = $config->read();
		$this->assertSame(['foo'], $data['ignore']);
		$this->assertSame('bar', $data['redirects'][0]['to']);
		$this->assertSame($data, $this->entry($file)['data']);
	}

	public function testGet(): void
	{
		$config = $this->config(__DIR__ . '/fixtures/redirects.yml');
		$this->assertCount(3, $config->get('redirects'));
		$this->assertNull($config->get('ignore'));
	}

	public function testReadInvalidFile(): void
	{
		$file    = $this->tmp . '/retour.yml';
		$content = "redirects:\n  - from: foo\n    to: [bar\n";
		F::write($file, $content);

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $file
			]
		]);

		// the site keeps running without redirects
		$config = Retour::instance($app)->config();
		$this->assertNull($config->get('redirects'));
		$this->assertInstanceOf(LogicException::class, $config->error());
		$this->assertInstanceOf(ParseException::class, $config->error()->getPrevious());

		// but the file must not be overwritten
		try {
			$config->update(['ignore' => ['foo']]);
			$this->fail('Writing an unreadable config file must fail');
		} catch (LogicException) {
			$this->assertSame($content, F::read($file));
		}
	}

	public function testReadInvalidFileOnBoot(): void
	{
		$file = $this->tmp . '/retour.yml';
		F::write($file, "redirects:\n\t- from: foo\n");

		Retour::reset();

		// the plugin's routes are built when the app boots
		$app = new App([
			'roots'   => ['index' => $this->tmp],
			'options' => ['distantnative.retour.config' => $file]
		]);

		$this->assertInstanceOf(LogicException::class, Retour::instance($app)->config()->error());
	}

	public function testReadMissingFile(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $this->tmp . '/missing.yml'
			]
		]);

		$config = Retour::instance($app)->config();
		$this->assertSame([], $config->read());
		$this->assertNull($config->get('redirects'));
	}

	public function testReadUnreadableFile(): void
	{
		if (function_exists('posix_getuid') === true && posix_getuid() === 0) {
			$this->markTestSkipped('Root can read any file');
		}

		F::write($file = $this->tmp . '/retour.yml', "redirects: []\n");
		chmod($file, 0000);

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $file
			]
		]);

		try {
			$config = Retour::instance($app)->config();
			$this->assertNull($config->get('redirects'));
			$this->assertInstanceOf(LogicException::class, $config->error());
		} finally {
			chmod($file, 0644);
		}
	}

	public function testRoot(): void
	{
		$config = Retour::instance()->config();
		$this->assertSame($this->tmp . '/site/config/retour.yml', $config->root());

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $file = $this->tmp . '/meow.json'
			]
		]);
		$config = Retour::instance($app)->config();
		$this->assertSame($file, $config->root());

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => fn () => $file
			]
		]);
		$config = Retour::instance($app)->config();
		$this->assertSame($file, $config->root());

		// paths that happen to be function names are not called
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => 'phpversion'
			]
		]);
		$config = Retour::instance($app)->config();
		$this->assertSame('phpversion', $config->root());
	}

	public function testUpdate(): void
	{
		$config = $this->config($file = $this->tmp . '/test.yml');
		$this->assertNull($config->get('foo'));

		$this->assertSame(['foo' => 'bar'], $config->update(['foo' => 'bar']));
		$this->assertSame('bar', $config->get('foo'));

		// keys are merged into the current data
		$this->assertSame(['foo' => 'new', 'baz' => 'qux'], $config->update(['foo' => 'new', 'baz' => 'qux']));

		// a closure returns the new data from the current one
		$data = $config->update(fn (array $data) => [...$data, 'foo' => strtoupper($data['foo'])]);
		$this->assertSame(['foo' => 'NEW', 'baz' => 'qux'], $data);
		$this->assertSame($data, Data::read($file));
	}

	public function testUpdateEmptyFile(): void
	{
		F::write($file = $this->tmp . '/test.yml', '');

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $file
			]
		]);

		$config = Retour::instance($app)->config();
		$this->assertSame(['foo' => 'bar'], $config->update(['foo' => 'bar']));
		$this->assertSame(['foo' => 'bar'], Data::read($file));
	}

	public function testUpdateFailing(): void
	{
		Data::write($file = $this->tmp . '/test.yml', ['foo' => 'bar']);

		$config = $this->config($file);

		try {
			$config->update(fn () => throw new LogicException(message: 'Nope'));
			$this->fail('The exception must be passed on');
		} catch (LogicException $e) {
			$this->assertSame('Nope', $e->getMessage());
			$this->assertSame(['foo' => 'bar'], Data::read($file));
		}
	}

	public function testUpdateFileBrokenAfterRead(): void
	{
		Data::write($file = $this->tmp . '/test.yml', ['redirects' => []]);

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $file
			]
		]);

		$config = Retour::instance($app)->config();
		$this->assertNull($config->error());

		// the file gets broken (e.g. by hand) after it has been read
		F::write($file, $content = "redirects:\n\t- from: foo\n");

		try {
			$config->update(['ignore' => ['foo']]);
			$this->fail('Writing an unreadable config file must fail');
		} catch (LogicException $e) {
			$this->assertStringStartsWith('Retour: The config file could not be read', $e->getMessage());
			$this->assertSame($content, F::read($file));
		}
	}

	public function testUpdateKeepsConcurrentChanges(): void
	{
		Data::write($file = $this->tmp . '/test.yml', ['redirects' => []]);

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $file
			]
		]);

		$config = Retour::instance($app)->config();

		// another request changes the file in the meantime
		Data::write($file, ['redirects' => [], 'ignore' => ['foo']]);

		$expected = ['redirects' => [['from' => 'bar']], 'ignore' => ['foo']];
		$this->assertSame($expected, $config->update(['redirects' => [['from' => 'bar']]]));
		$this->assertSame($expected, Data::read($file));
	}
}
