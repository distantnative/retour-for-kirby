<?php

namespace Kirby\Retour;

use Kirby\Cms\App;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Retour::class)]
class RetourTest extends TestCase
{
	public function testConstruct(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => __DIR__ . '/fixtures/redirects.yml'
			]
		]);

		$redirects = Retour::instance($app)->redirects();
		$this->assertSame(3, $redirects->count());
	}

	public function testConfig(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => __DIR__ . '/tmp/retour.yml'
			]
		]);

		$config = Retour::instance($app)->config();
		$this->assertInstanceOf(Config::class, $config);
	}

	public function testHasLog(): void
	{
		$retour = Retour::instance();
		$this->assertTrue($retour->hasLog());

		// no matching ignore
		$app = $this->app(['distantnative.retour.logs' => false]);
		$retour = Retour::instance($app);
		$this->assertFalse($retour->hasLog());
	}

	public function testIgnore(): void
	{
		// no ignore
		$path   = 'foo/bar';
		$retour = Retour::instance();
		$this->assertFalse($retour->ignore($path));

		// no matching ignore
		$app = $this->app([
			'distantnative.retour.ignore' => [
				'foo',
				'bar'
			]
		]);
		$retour = Retour::instance($app);
		$this->assertFalse($retour->ignore($path));

		// direct matching string ignore
		$app = $this->app([
			'distantnative.retour.ignore' => [
				'foo/bar',
				'bar'
			]
		]);
		$retour = Retour::instance($app);
		$this->assertTrue($retour->ignore($path));

		// ignore with wildcard placeholder
		$app = $this->app([
			'distantnative.retour.ignore' => [
				'foo/(:any)',
				'bar'
			]
		]);
		$retour = Retour::instance($app);
		$this->assertTrue($retour->ignore($path));
	}

	public function testInstance(): void
	{
		$app    = App::instance();
		$retour = Retour::instance();
		$this->assertInstanceOf(Retour::class, $retour);
		$this->assertSame($app, $retour->kirby());

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => __DIR__ . '/tmp/retour.yml'
			]
		]);
		$retour = Retour::instance($app);
		$this->assertInstanceOf(Retour::class, $retour);
		$this->assertSame($app, $retour->kirby());
	}

	public function testKirby(): void
	{
		$kirby = Retour::instance()->kirby();
		$this->assertInstanceOf(App::class, $kirby);
	}

	public function testLog(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.database' => __DIR__ . '/tmp/test.sqlite'
			]
		]);

		$log = Retour::instance($app)->log();
		$this->assertInstanceOf(Log::class, $log);
	}

	public function testLogDisabled(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.logs'     => false,
				'distantnative.retour.database' => __DIR__ . '/tmp/test.sqlite'
			]
		]);

		$log = Retour::instance($app)->log();
		$this->assertInstanceOf(LogDisabled::class, $log);
	}

	public function testOption()
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.foo'   => 'bar'
			]
		]);

		$retour = Retour::instance($app);
		$this->assertSame('bar', $retour->option('foo'));
		$this->assertSame('simpson', $retour->option('homer', 'simpson'));
	}

	public function testRedirects(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => __DIR__ . '/tmp/retour.yml'
			]
		]);

		$redirects = Retour::instance($app)->redirects();
		$this->assertInstanceOf(Redirects::class, $redirects);
	}

	public function testSite(): void
	{
		$site = Retour::instance()->site();
		$this->assertSame('//', $site);

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.site' => 'meow.com'
			]
		]);

		$site = Retour::instance($app)->site();
		$this->assertSame('meow.com/', $site);


		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.site' => false
			]
		]);

		$site = Retour::instance($app)->site();
		$this->assertFalse($site);
	}
}
