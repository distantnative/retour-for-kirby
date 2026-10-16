<?php

namespace Kirby\Retour;

use Kirby\Cms\App;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Retour::class)]
class RetourTest extends TestCase
{
	public function testConfig(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $this->tmp . '/retour.yml'
			]
		]);

		$config = Retour::instance($app)->config();
		$this->assertInstanceOf(Config::class, $config);
	}

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
				'distantnative.retour.config' => $this->tmp . '/retour.yml'
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
				'distantnative.retour.database' => $this->tmp . '/test.sqlite'
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
				'distantnative.retour.database' => $this->tmp . '/test.sqlite'
			]
		]);

		$this->assertNull(Retour::instance($app)->log());
	}

	public function testOption(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.foo'   => 'bar'
			]
		]);

		$retour = Retour::instance($app);
		$this->assertSame('bar', $retour->option('foo'));
		$this->assertNull($retour->option('homer'));
	}

	public function testOptionDefaults(): void
	{
		$retour = Retour::instance();

		$this->assertSame($this->tmp . '/site/config/retour.yml', $retour->option('config')());
		$this->assertSame($this->tmp . '/site/logs/retour/log.sqlite', $retour->option('database')());
		$this->assertFalse($retour->option('deleteAfter'));
		$this->assertSame([], $retour->option('ignore'));
		$this->assertTrue($retour->option('logs'));
		$this->assertFalse($retour->option('preserveQuery'));
		$this->assertTrue($retour->option('site'));
		$this->assertNull($retour->option('status'));
	}

	public function testOptionOverrides(): void
	{
		// flat option keys
		$retour = Retour::instance($this->app([
			'distantnative.retour.logs'   => false,
			'distantnative.retour.ignore' => ['foo'],
			'distantnative.retour.status' => 301
		]));

		$this->assertFalse($retour->option('logs'));
		$this->assertSame(['foo'], $retour->option('ignore'));
		$this->assertSame(301, $retour->option('status'));
		$this->assertFalse($retour->option('preserveQuery'));

		// nested plugin options
		$retour = Retour::instance($this->app([
			'distantnative.retour' => [
				'logs'   => false,
				'ignore' => ['bar']
			]
		]));

		$this->assertFalse($retour->option('logs'));
		$this->assertSame(['bar'], $retour->option('ignore'));
		$this->assertTrue($retour->option('site'));
	}

	public function testRedirects(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $this->tmp . '/retour.yml'
			]
		]);

		$redirects = Retour::instance($app)->redirects();
		$this->assertInstanceOf(Redirects::class, $redirects);
	}

	public function testReset(): void
	{
		$retour = Retour::instance();
		Retour::reset();
		$this->assertNotSame($retour, Retour::instance());
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
