<?php

namespace Kirby\Retour\Panel;

use Kirby\Exception\LogicException;
use Kirby\Filesystem\F;
use Kirby\Http\Router;
use Kirby\Panel\Redirect;
use Kirby\Retour\LogTest;
use Kirby\Retour\Retour;
use Kirby\Retour\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Throwable;

#[CoversClass(View::class)]
class ViewTest extends TestCase
{
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
		LogTest::legacy($this->tmp . '/log.sqlite');

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

	protected function view(string $tab, array $options = []): View
	{
		Retour::instance($this->app([
			'distantnative.retour.config'   => $this->tmp . '/retour.yml',
			'distantnative.retour.database' => $this->tmp . '/log.sqlite',
			...$options
		]));

		return new View($tab);
	}
}
