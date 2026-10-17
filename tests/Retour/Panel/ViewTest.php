<?php

namespace Kirby\Retour\Panel;

use Kirby\Data\Data;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\F;
use Kirby\Http\Router;
use Kirby\Panel\Redirect;
use Kirby\Retour\Log\MigrationsTest;
use Kirby\Retour\Retour;
use Kirby\Retour\TestCase;
use Kirby\Toolkit\Pagination;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(View::class)]
class ViewTest extends TestCase
{
	public function testButtons(): void
	{
		$buttons = $this->view('redirects')->buttons()->render();
		$this->assertCount(1, $buttons);
		$this->assertSame('retour/redirects/create', $buttons[0]['props']['drawer']);

		$buttons = $this->view('failures')->buttons()->render();
		$this->assertCount(2, $buttons);
		$this->assertSame('retour/failures/ignores', $buttons[0]['props']['dialog']);
		$this->assertSame('retour/failures/flush', $buttons[1]['props']['dialog']);
	}

	public function testButtonsFromConfig(): void
	{
		$view = $this->view('failures', [
			'panel' => [
				'viewButtons' => [
					'retour.failures' => ['flush']
				]
			]
		]);

		$buttons = $view->buttons()->render();
		$this->assertCount(1, $buttons);
		$this->assertSame('retour/failures/flush', $buttons[0]['props']['dialog']);
	}

	public function testLoad(): void
	{
		$view = $this->view('failures')->load();
		$this->assertSame('k-retour-failures-view', $view->component);
		$this->assertSame('failures', $view->props()['tab']);
	}

	public function testLoadFailuresWithoutLog(): void
	{
		$view = $this->view('failures', [
			'distantnative.retour.logs' => false
		]);

		try {
			$view->load();
			$this->fail('The failures tab must redirect without log');
		} catch (Redirect $redirect) {
			$this->assertStringEndsWith('/panel/retour/redirects', $redirect->location());
		}
	}

	public function testLoadMigratesLog(): void
	{
		MigrationsTest::legacy($this->tmp . '/log.sqlite');

		$this->view('redirects')->load();

		$version = Retour::instance()->log()->database()->connection()->query('PRAGMA user_version')->fetchColumn();
		$this->assertGreaterThan(0, (int)$version);
	}

	public function testLoadWithUnreadableConfig(): void
	{
		F::write($this->tmp . '/retour.yml', "redirects:\n\t- from: foo\n");

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Retour: The config file could not be read');

		$this->view('redirects')->load();
	}

	public function testPattern(): void
	{
		$areas   = require dirname(__DIR__, 3) . '/src/extensions/areas.php';
		$pattern = $areas['retour']()['views'][1]['pattern'];
		$router  = new Router([
			[
				'pattern' => $pattern,
				'action'  => fn (string $tab) => $tab
			]
		]);

		$this->assertSame('redirects', $router->call('retour/redirects'));
		$this->assertSame('failures', $router->call('retour/failures'));

		foreach (['retour/foo', 'retour/redirects-foo', 'retour/failures/foo'] as $path) {
			try {
				$router->call($path);
				$this->fail('Unknown tabs must not be routed: ' . $path);
			} catch (Throwable $e) {
				$this->assertSame(404, $e->getCode(), $path);
			}
		}
	}

	public function testPropsFailures(): void
	{
		$view = $this->view('failures', query: [
			'page'       => '2',
			'searchterm' => ' wp- ',
			'sortBy'     => 'path desc'
		]);

		$log = Retour::instance()->log();
		$log->add(['path' => 'foo']);

		for ($i = 10; $i < 35; $i++) {
			$log->add(['path' => 'wp-' . $i]);
		}

		$props = $view->props();
		$this->assertSame(['wp-14', 'wp-13', 'wp-12', 'wp-11', 'wp-10'], array_column($props['data'], 'path'));
		$this->assertSame(2, $props['pagination']['page']);
		$this->assertSame(25, $props['pagination']['total']);
		$this->assertSame('wp-', $props['searchterm']);
		$this->assertSame('path desc', $props['sortBy']);
	}

	public function testPropsOutOfRange(): void
	{
		Data::write($this->tmp . '/retour.yml', [
			'redirects' => [['from' => 'foo', 'status' => 301]]
		]);

		// like the Panel router, which clamps pages
		// instead of throwing, e.g. after deleting the last row
		$validate = Pagination::$validate;
		Pagination::$validate = false;

		try {
			$props = $this->view('redirects', query: ['page' => '5'])->props();
			$this->assertSame(1, $props['pagination']['page']);
			$this->assertSame(['foo'], array_column($props['data'], 'from'));

			$view = $this->view('failures', query: ['page' => '5']);
			Retour::instance()->log()->add(['path' => 'bar']);

			$props = $view->props();
			$this->assertSame(1, $props['pagination']['page']);
			$this->assertSame(['bar'], array_column($props['data'], 'path'));
		} finally {
			Pagination::$validate = $validate;
		}
	}

	public function testPropsRedirects(): void
	{
		Data::write($this->tmp . '/retour.yml', [
			'redirects' => [
				['from' => 'b', 'to' => 'x', 'status' => 301],
				['from' => 'a', 'to' => 'y', 'status' => 302, 'comment' => 'Old blog'],
				['from' => 'c', 'to' => 'blog', 'status' => 301],
				['from' => 'd', 'to' => 'z', 'status' => 410]
			]
		]);

		// unsorted in their order of precedence
		$props = $this->view('redirects')->props();
		$this->assertSame(['b', 'a', 'c', 'd'], array_column($props['data'], 'from'));
		$this->assertSame(4, $props['pagination']['total']);
		$this->assertNull($props['searchterm']);
		$this->assertNull($props['sortBy']);

		// path, target, status code and comment
		$props = $this->view('redirects', query: ['searchterm' => 'BLOG', 'sortBy' => 'from desc'])->props();
		$this->assertSame(['c', 'a'], array_column($props['data'], 'from'));
		$this->assertSame(2, $props['pagination']['total']);

		$props = $this->view('redirects', query: ['searchterm' => '410'])->props();
		$this->assertSame(['d'], array_column($props['data'], 'from'));

		// empty parameters are ignored
		$props = $this->view('redirects', query: ['searchterm' => ' ', 'sortBy' => ''])->props();
		$this->assertSame(['b', 'a', 'c', 'd'], array_column($props['data'], 'from'));
		$this->assertNull($props['searchterm']);
	}

	protected function view(
		string $tab,
		array $options = [],
		array $query = []
	): View {
		// view buttons are only collected from the areas of a logged-in user
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config'   => $this->tmp . '/retour.yml',
				'distantnative.retour.database' => $this->tmp . '/log.sqlite',
				...$options
			],
			'request' => [
				'query' => $query
			],
			'users' => [
				['email' => 'admin@getkirby.com', 'role' => 'admin']
			]
		]);

		$app->impersonate('admin@getkirby.com');
		Retour::instance($app);

		return new View($tab);
	}
}
