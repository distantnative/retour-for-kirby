<?php

namespace Kirby\Retour;

use Kirby\Data\Data;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;

#[CoversNothing]
class RoutesTest extends TestCase
{
	public function testLoadsRedirectsOnlyWhenReached(): void
	{
		$instance = new ReflectionProperty(Retour::class, 'instance');
		Retour::reset();

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $this->tmp . '/retour.yml',
				'distantnative.retour.logs'   => false
			]
		]);

		$app->call('api/system');
		$this->assertNull($instance->getValue());

		$app->call('missing');
		$this->assertSame($app, $instance->getValue()->kirby());
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
		$this->assertSame([], $log->fails('2000-01-01', '2099-12-31')->values());
	}

	public static function matchProvider(): array
	{
		return [
			'plain'          => [[['old', 'new']], 'old', 'new'],
			'pattern'        => [[['blog/(:num)/(:any)', 'news/$2-$1']], 'blog/2024/post', 'news/post-2024'],
			'pattern first'  => [[['blog/(:any)', 'pattern'], ['blog/old', 'plain']], 'blog/old', 'pattern'],
			'plain first'    => [[['blog/old', 'plain'], ['blog/(:any)', 'pattern']], 'blog/old', 'plain'],
			'same path'      => [[['old', 'first'], ['/old/', 'second']], 'old', 'first'],
			'slashes'        => [[['/old/', 'new']], 'old/', 'new'],
			'case'           => [[['Old', 'new']], 'old', null],
			'case pattern'   => [[['Blog/(:any)', 'new']], 'blog/old', null],
			'regex chars'    => [[['old.html', 'new']], 'old-html', null],
			'encoded'        => [[['caf%C3%A9', 'new']], 'caf%C3%A9', 'new'],
			'not decoded'    => [[['café', 'new']], 'caf%C3%A9', null],
			'disabled'       => [[['old', 'plain', 'disabled'], ['(:any)', 'pattern']], 'old', 'pattern'],
			'deleted target' => [[['old', 'page://missing'], ['(:any)', 'pattern']], 'old', null],
		];
	}

	/**
	 * Matches like Kirby's router: in order, case-sensitive,
	 * only GET and HEAD, and without trying further
	 * redirects if the target doesn't exist
	 */
	#[DataProvider('matchProvider')]
	public function testRenderMatches(array $redirects, string $path, string|null $to): void
	{
		foreach ([true, false] as $priority) {
			Data::write($file = $this->tmp . '/retour.yml', [
				'redirects' => array_map(fn (array $redirect) => [
					'from'     => $redirect[0],
					'to'       => $redirect[1],
					'status'   => $redirect[2] ?? 301,
					'priority' => $priority
				], $redirects)
			]);

			Retour::reset();

			$app = $this->kirby->clone([
				'options' => [
					'distantnative.retour.config' => $file,
					'distantnative.retour.logs'   => false
				],
				'urls' => [
					'index' => 'https://example.com'
				]
			]);

			foreach (['GET', 'HEAD', 'POST'] as $method) {
				$expected = $to !== null && $method !== 'POST' ? 'https://example.com/' . $to : null;
				$response = $app->render($path, $method);
				$this->assertSame($expected, $response->header('Location'), $method . ($priority === true ? ' with priority' : ''));
			}
		}
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
		$fails = array_column($log->fails('2000-01-01', '2099-12-31')->values(), 'hits', 'path');
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
		$fails = array_column($log->fails('2000-01-01', '2099-12-31')->values(), 'hits', 'path');
		ksort($fails);

		$this->assertSame(['fallback' => 1, 'priority' => 1], $fails);
		$this->assertSame([], $log->redirects('2000-01-01', '2099-12-31'));
	}
}
