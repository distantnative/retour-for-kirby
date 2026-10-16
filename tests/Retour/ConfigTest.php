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
	public function testData(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $file = __DIR__ . '/fixtures/redirects.yml'
			]
		]);
		$config = Retour::instance($app)->config();
		$data   = $config->data();
		$this->assertCount(3, $data['redirects']);
		$this->assertCount(3, $config->data('redirects'));
	}

	public function testFile(): void
	{
		$config = Retour::instance()->config();
		$this->assertSame($this->tmp . '/site/config/retour.yml', $config->file());

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $file = $this->tmp . '/meow.json'
			]
		]);
		$config = Retour::instance($app)->config();
		$this->assertSame($file, $config->file());

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => fn () => $file
			]
		]);
		$config = Retour::instance($app)->config();
		$this->assertSame($file, $config->file());
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
		$this->assertSame([], $config->data());
		$this->assertInstanceOf(LogicException::class, $config->error());
		$this->assertInstanceOf(ParseException::class, $config->error()->getPrevious());

		// but the file must not be overwritten
		try {
			$config->write('ignore', ['foo']);
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
		$this->assertSame([], $config->data());
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
			$this->assertSame([], $config->data());
			$this->assertInstanceOf(LogicException::class, $config->error());
		} finally {
			chmod($file, 0644);
		}
	}

	public function testWrite(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $file = $this->tmp . '/test.yml'
			]
		]);
		$config = Retour::instance($app)->config();
		$data   = $config->data();
		$this->assertSame([], $data);

		$data = $config->write($new = ['foo' => 'bar']);
		$this->assertSame($new, $data);
		$this->assertSame($new, $config->data());

		$data = $config->write('foo', 'new');
		$this->assertSame(['foo' => 'new'], $data);
		$this->assertSame(['foo' => 'new'], $config->data());
	}

	public function testWriteEmptyFile(): void
	{
		F::write($file = $this->tmp . '/test.yml', '');

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $file
			]
		]);

		$config = Retour::instance($app)->config();
		$this->assertSame(['foo' => 'bar'], $config->write('foo', 'bar'));
		$this->assertSame(['foo' => 'bar'], Data::read($file));
	}

	public function testWriteFileBrokenAfterRead(): void
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
			$config->write('ignore', ['foo']);
			$this->fail('Writing an unreadable config file must fail');
		} catch (LogicException $e) {
			$this->assertStringStartsWith('Retour: The config file could not be read', $e->getMessage());
			$this->assertSame($content, F::read($file));
		}
	}

	public function testWriteKeepsConcurrentChanges(): void
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
		$this->assertSame($expected, $config->write('redirects', [['from' => 'bar']]));
		$this->assertSame($expected, Data::read($file));
	}
}
