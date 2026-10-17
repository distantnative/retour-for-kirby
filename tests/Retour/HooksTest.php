<?php

namespace Kirby\Retour;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Http\Response;
use Kirby\Http\Route;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class HooksTest extends TestCase
{
	/**
	 * Applies the `route:after` hooks like the router does
	 * and returns the (possibly replaced) result
	 */
	protected function apply(
		App $app,
		string $path,
		string $method = 'GET',
		mixed $result = null,
		bool $final = true
	): mixed {
		return $app->apply('route:after', [
			'route'  => new Route($path, $method, fn () => null),
			'path'   => $path,
			'method' => $method,
			'result' => $result,
			'final'  => $final
		], 'result');
	}

	public function testHeadRequestIsNotLoggedAsFailure(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.database' => $this->tmp . '/test.sqlite',
			]
		]);
		$retour = Retour::instance($app);

		$this->assertNull($this->apply($app, 'missing', 'HEAD'));
		$this->assertSame([], $retour->log()->fails('2000-01-01', '2099-12-31')->values());

		$this->assertNull($this->apply($app, 'missing'));
		$this->assertCount(1, $retour->log()->fails('2000-01-01', '2099-12-31')->values());
	}

	public function testIgnoredIpIsNotLogged(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.ignoreIps' => ['192.0.2.0/24'],
				'distantnative.retour.database'  => $this->tmp . '/test.sqlite',
			]
		]);
		$retour = Retour::instance($app);

		$app->visitor()->ip('192.0.2.1');
		$this->assertNull($this->apply($app, 'missing'));
		$this->assertSame([], $retour->log()->fails('2000-01-01', '2099-12-31')->values());

		$app->visitor()->ip('198.51.100.1');
		$this->assertNull($this->apply($app, 'missing'));
		$this->assertCount(1, $retour->log()->fails('2000-01-01', '2099-12-31')->values());
	}

	public function testIgnoredPathIsNotLogged(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.ignore'   => ['api/(:any)'],
				'distantnative.retour.database' => $this->tmp . '/test.sqlite',
			]
		]);
		$retour = Retour::instance($app);

		$result = $this->apply($app, 'api/users');
		$this->assertNull($result);
		$this->assertSame([], $retour->log()->fails('2000-01-01', '2099-12-31')->values());
	}

	public function testIgnoredPathIsRedirected(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.ignore'   => ['wp-(:all)'],
				'distantnative.retour.config'   => $this->tmp . '/retour.yml',
				'distantnative.retour.database' => $this->tmp . '/test.sqlite',
			],
			'site' => [
				'children' => [
					['slug' => 'target']
				]
			]
		]);

		$retour = Retour::instance($app);
		$retour->redirects()->prepend(new Redirect([
			'from'   => 'wp-content/(:all)',
			'to'     => 'target',
			'status' => 200
		]));

		$result = $this->apply($app, 'wp-content/uploads/foo.jpg');
		$this->assertInstanceOf(Page::class, $result);
		$this->assertSame('target', $result->id());

		// logged as hit of the redirect, not as failure
		$this->assertSame(1, $retour->log()->redirects('2000-01-01', '2099-12-31')['wp-content/(:all)']['hits']);
		$this->assertSame([], $retour->log()->fails('2000-01-01', '2099-12-31')->values());
	}

	public function testLogsFailedRouteAs404(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.database' => $this->tmp . '/test.sqlite',
			]
		]);
		$retour = Retour::instance($app);

		$beforeCount = count($retour->log()->fails('2000-01-01', '2099-12-31'));

		$this->apply($app, 'not-found');

		$afterCount = count($retour->log()->fails('2000-01-01', '2099-12-31'));
		$this->assertSame($beforeCount + 1, $afterCount);
	}

	public function testMatchesNonPriorityRedirect(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.logs'   => false,
				'distantnative.retour.config' => $this->tmp . '/retour.yml',
			],
			'site' => [
				'children' => [
					['slug' => 'target']
				]
			]
		]);

		$retour = Retour::instance($app);
		$retour->redirects()->prepend(new Redirect([
			'from'     => 'old-path',
			'to'       => 'target',
			'status'   => 200,
			'priority' => false,
		]));

		$result = $this->apply($app, 'old-path');
		$this->assertInstanceOf(Page::class, $result);
	}

	public function testMatchesNonPriorityRedirectForHeadRequest(): void
	{
		$app = $this->kirby->clone([
			'request' => [
				'method' => 'HEAD'
			],
			'options' => [
				'distantnative.retour.config'   => $this->tmp . '/retour.yml',
				'distantnative.retour.database' => $this->tmp . '/test.sqlite',
			],
			'site' => [
				'children' => [
					['slug' => 'target']
				]
			]
		]);

		$retour = Retour::instance($app);
		$retour->redirects()->prepend(new Redirect([
			'from'   => 'old-path',
			'to'     => 'target',
			'status' => 301
		]));

		$result = $this->apply($app, 'old-path', 'HEAD');
		$this->assertInstanceOf(Response::class, $result);
		$this->assertSame(301, $result->code());

		// neither logged as failure nor as hit
		$this->assertSame([], $retour->log()->fails('2000-01-01', '2099-12-31')->values());
		$this->assertSame([], $retour->log()->redirects('2000-01-01', '2099-12-31'));
	}

	public function testSkips404LoggingWhenDisabled(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.logs'     => false,
				'distantnative.retour.database' => $this->tmp . '/test.sqlite',
			]
		]);
		Retour::instance($app);

		// With logging disabled the hook must not throw and must not log
		$result = $this->apply($app, 'some/missing-page');
		$this->assertNull($result);
		$this->assertFileDoesNotExist($this->tmp . '/test.sqlite');
	}

	public function testSkipsNonEmptyResult(): void
	{
		$app = $this->kirby->clone([
			'options' => ['distantnative.retour.logs' => false]
		]);
		Retour::instance($app);

		// result is already set → hook should not interfere
		$result = $this->apply($app, 'some/path', result: 'existing');
		$this->assertSame('existing', $result);
	}

	public function testSkipsNonFinalRoutes(): void
	{
		$app = $this->kirby->clone([
			'options' => ['distantnative.retour.logs' => false]
		]);
		Retour::instance($app);

		// $final = false → hook should not process the route
		$result = $this->apply($app, 'some/path', final: false);
		$this->assertNull($result);
	}
}
