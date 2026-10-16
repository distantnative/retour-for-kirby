<?php

namespace Kirby\Retour;

use Closure;
use Kirby\Cms\Page;
use Kirby\Http\Route;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class HooksTest extends TestCase
{
	protected function hook(): Closure
	{
		$hooks = require dirname(__DIR__, 2) . '/src/extensions/hooks.php';
		return $hooks['route:after'];
	}

	protected function route(): Route
	{
		return new Route('', '', fn () => null);
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

		$result = ($this->hook())($this->route(), 'api/users', 'GET', null, true);
		$this->assertNull($result);
		$this->assertSame([], $retour->log()->fails('2000-01-01', '2099-12-31'));
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

		$result = ($this->hook())($this->route(), 'wp-content/uploads/foo.jpg', 'GET', null, true);
		$this->assertInstanceOf(Page::class, $result);
		$this->assertSame('target', $result->id());

		// logged as hit of the redirect, not as failure
		$this->assertSame(1, $retour->log()->redirect('wp-content/(:all)', '2000-01-01', '2099-12-31')['hits']);
		$this->assertSame([], $retour->log()->fails('2000-01-01', '2099-12-31'));
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

		($this->hook())($this->route(), 'not-found', 'GET', null, true);

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

		$result = ($this->hook())($this->route(), 'old-path', 'GET', null, true);
		$this->assertInstanceOf(Page::class, $result);
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
		$result = ($this->hook())($this->route(), 'some/missing-page', 'GET', null, true);
		$this->assertNull($result);
	}

	public function testSkipsNonEmptyResult(): void
	{
		$app = $this->kirby->clone([
			'options' => ['distantnative.retour.logs' => false]
		]);
		Retour::instance($app);

		// result is already set → hook should not interfere
		$result = ($this->hook())($this->route(), 'some/path', 'GET', 'existing', true);
		$this->assertNull($result);
	}

	public function testSkipsNonFinalRoutes(): void
	{
		$app = $this->kirby->clone([
			'options' => ['distantnative.retour.logs' => false]
		]);
		Retour::instance($app);

		// $final = false → hook should not process the route
		$result = ($this->hook())($this->route(), 'some/path', 'GET', null, false);
		$this->assertNull($result);
	}
}
