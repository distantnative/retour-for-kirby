<?php

namespace Kirby\Retour;

use PHPUnit\Framework\Attributes\CoversClass;

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
		$this->assertSame(__DIR__ . '/tmp/site/config/retour.yml', $config->file());

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $file = __DIR__ . '/tmp/meow.json'
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

	public function testRetour(): void
	{
		$retour  = Retour::instance();
		$config  = $retour->config();
		$this->assertInstanceOf(Retour::class, $config->retour());
		$this->assertSame($retour, $config->retour());
	}

	public function testWrite(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $file = __DIR__ . '/tmp/test.yml'
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
}
