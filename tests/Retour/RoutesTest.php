<?php

namespace Kirby\Retour;

use Kirby\Cms\App;
use Kirby\Data\Data;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class RoutesTest extends TestCase
{
	public function testExtension(): void
	{
		$extension = App::plugin('distantnative/retour')->extends()['routes'];

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $this->tmp . '/test.yml',
				'distantnative.retour.logs'   => false
			]
		]);

		$this->assertSame(0, count($extension($app)));

		$retour = Retour::instance($app);
		$retour->redirects()->prepend(new Redirect([
			'from' => 'foo',
			'to'   => 'bar'
		]));

		$this->assertSame(0, count($extension($app)));

		$retour->redirects()->prepend(new Redirect([
			'from'     => 'foo',
			'to'       => 'bar',
			'priority' => true,
			'status'   => 307
		]));

		$this->assertSame(1, count($extension($app)));
	}

	public function testRenderFromIgnoredIp(): void
	{
		Data::write($file = $this->tmp . '/retour.yml', [
			'redirects' => [
				['from' => 'priority', 'to' => 'target', 'status' => 301, 'priority' => true],
				['from' => 'fallback', 'to' => 'target', 'status' => 301]
			]
		]);

		Retour::reset();

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config'    => $file,
				'distantnative.retour.database'  => $this->tmp . '/log.sqlite',
				'distantnative.retour.ignoreIps' => ['192.0.2.1']
			],
			'site' => [
				'children' => [
					['slug' => 'target']
				]
			]
		]);

		$app->visitor()->ip('192.0.2.1');

		// still redirected, but neither logged as hit nor as failure
		$this->assertSame(301, $app->render('priority')->code());
		$this->assertSame(301, $app->render('fallback')->code());
		$this->assertSame(404, $app->render('missing')->code());

		$log = Retour::instance($app)->log();
		$this->assertSame([], $log->redirects('2000-01-01', '2099-12-31'));
		$this->assertSame([], $log->fails('2000-01-01', '2099-12-31'));
	}

	public function testRenderRedirects(): void
	{
		// plugin routes are registered when the app is created
		Data::write($file = $this->tmp . '/retour.yml', [
			'redirects' => [
				['from' => 'priority', 'to' => 'target', 'status' => 301, 'priority' => true],
				['from' => 'fallback', 'to' => 'target', 'status' => 308]
			]
		]);

		Retour::reset();

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $file,
				'distantnative.retour.logs'   => false
			],
			'urls' => [
				'index' => 'https://example.com'
			],
			'site' => [
				'children' => [
					['slug' => 'target']
				]
			]
		]);

		// priority redirects are plugin routes, the others
		// are resolved in the `route:after` hook
		foreach (['priority' => 301, 'fallback' => 308] as $path => $code) {
			foreach (['GET', 'HEAD'] as $method) {
				$response = $app->render($path, $method);
				$this->assertSame($code, $response->code(), $method . ' ' . $path);
				$this->assertSame('https://example.com/target', $response->header('Location'), $method . ' ' . $path);
			}
		}
	}

	public function testRenderRedirectsToDeletedPage(): void
	{
		Data::write($file = $this->tmp . '/retour.yml', [
			'redirects' => [
				['from' => 'priority', 'to' => 'page://missing', 'status' => 301, 'priority' => true],
				['from' => 'fallback', 'to' => 'page://missing', 'status' => 301]
			]
		]);

		Retour::reset();

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config'   => $file,
				'distantnative.retour.database' => $this->tmp . '/log.sqlite'
			],
			'urls' => [
				'index' => 'https://example.com'
			]
		]);

		foreach (['priority', 'fallback'] as $path) {
			$this->assertSame(404, $app->render($path)->code(), $path);
		}

		// logged once as failure each, not as redirect hit
		$log   = Retour::instance($app)->log();
		$fails = array_column($log->fails('2000-01-01', '2099-12-31'), 'hits', 'path');
		ksort($fails);

		$this->assertSame(['fallback' => 1, 'priority' => 1], $fails);
		$this->assertSame([], $log->redirects('2000-01-01', '2099-12-31'));
	}

	public function testRenderRedirectsWithStatus200ToMissingPage(): void
	{
		Data::write($file = $this->tmp . '/retour.yml', [
			'redirects' => [
				['from' => 'priority', 'to' => 'missing', 'status' => 200, 'priority' => true],
				['from' => 'fallback', 'to' => 'missing', 'status' => 200]
			]
		]);

		Retour::reset();

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config'   => $file,
				'distantnative.retour.database' => $this->tmp . '/log.sqlite'
			]
		]);

		foreach (['priority', 'fallback'] as $path) {
			$this->assertSame(404, $app->render($path)->code(), $path);
		}

		// logged once as failure each, not as redirect hit
		$log   = Retour::instance($app)->log();
		$fails = array_column($log->fails('2000-01-01', '2099-12-31'), 'hits', 'path');
		ksort($fails);

		$this->assertSame(['fallback' => 1, 'priority' => 1], $fails);
		$this->assertSame([], $log->redirects('2000-01-01', '2099-12-31'));
	}
}
