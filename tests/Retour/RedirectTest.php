<?php

namespace Kirby\Retour;

use Closure;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Response;
use Kirby\Http\Route;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Redirect::class)]
class RedirectTest extends TestCase
{
	public function testConstructWithoutPath(): void
	{
		$this->expectExceptionMessage('Route requires path');
		$this->expectException(InvalidArgumentException::class);
		new Redirect(from: '');
	}

	public function testFactoryLanguage(): void
	{
		$redirect = Redirect::factory(['from' => 'foo', 'language' => 'en']);
		$this->assertSame('en', $redirect->language());

		// the Panel sends an empty string for the language of the path
		$redirect = Redirect::factory(['from' => 'foo', 'language' => '']);
		$this->assertNull($redirect->language());
	}

	public function testFactoryNumericValues(): void
	{
		// e.g. unquoted numbers in hand-edited config files
		$redirect = Redirect::factory(['from' => 404, 'to' => 410, 'status' => 301]);
		$this->assertSame('404', $redirect->from());
		$this->assertSame('410', $redirect->to());
		$this->assertSame('404', $redirect->id());
	}

	public function testFactoryPathZero(): void
	{
		$this->assertSame('0', (new Redirect(from: '0'))->from());
		$this->assertSame('0', Redirect::factory(['from' => 0])->from());
	}

	public function testFactoryPriority(): void
	{
		// strings of config files and forms
		$redirect = Redirect::factory(['from' => 'foo', 'priority' => 'true']);
		$this->assertTrue($redirect->priority());

		$redirect = Redirect::factory(['from' => 'foo', 'priority' => 'false']);
		$this->assertFalse($redirect->priority());

		$redirect = Redirect::factory(['from' => 'foo', 'priority' => null]);
		$this->assertFalse($redirect->priority());
	}

	public function testFactoryStatus(): void
	{
		$redirect = Redirect::factory(['from' => 'foo', 'status' => '301']);
		$this->assertSame(301, $redirect->status());

		// empty values from the Panel and
		// hand-edited config files disable it
		foreach ([null, '', 'disabled', false, true, 0, '0'] as $status) {
			$redirect = Redirect::factory(['from' => 'foo', 'status' => $status]);
			$this->assertNull($redirect->status(), var_export($status, true));
			$this->assertFalse($redirect->isActive());
		}
	}

	public function testFactoryWithoutPath(): void
	{
		$this->expectExceptionMessage('Route requires path');
		$this->expectException(InvalidArgumentException::class);
		Redirect::factory([]);
	}

	public function testId(): void
	{
		$redirect = new Redirect(from: 'foo');
		$this->assertSame('foo', $redirect->id());

		$redirect = new Redirect(from: 'foo/bar');
		$this->assertSame('foo/bar', $redirect->id());
	}

	public function testIsActive(): void
	{
		$redirect = new Redirect(from: 'foo');
		$this->assertFalse($redirect->isActive());

		$redirect = new Redirect(from: 'foo', status: 507);
		$this->assertTrue($redirect->isActive());
	}

	public function testPriority(): void
	{
		$redirect = new Redirect(from: 'foo');
		$this->assertFalse($redirect->priority());

		$redirect = new Redirect(from: 'foo', priority: true);
		$this->assertTrue($redirect->priority());
	}

	public function testResolve(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.logs' => false
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'projects',
						'children' => [
							['slug' => 'project-a']
						]
					]
				]
			],
		]);

		Retour::instance($app);

		// resolves to a page
		$redirect = new Redirect(
			from: 'foo',
			to: 'projects/project-a',
			status: 200
		);

		$this->assertInstanceOf(Page::class, $redirect->resolve([]));

		// resolves with placeholder substitution
		$redirect = new Redirect(
			from: 'old/(:any)',
			to: 'projects/$1',
			status: 200
		);

		$this->assertInstanceOf(Page::class, $redirect->resolve(['project-a']));
	}

	public function testResolveDraft(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.logs' => false
			],
			'site' => [
				'children' => [
					['slug' => 'public']
				],
				'drafts' => [
					[
						'slug'     => 'secret',
						'content'  => ['uuid' => 'secret'],
						'children' => [
							['slug' => 'child']
						]
					]
				]
			],
		]);

		Retour::instance($app);

		$redirect = new Redirect(
			from: 'old/(:all)',
			to: '$1',
			status: 200
		);

		$this->assertInstanceOf(Page::class, $redirect->resolve(['public']));

		// drafts and their children are treated as not found
		foreach (['secret', 'secret/child', 'page://secret'] as $path) {
			$this->assertNull($redirect->resolve([$path]), $path);
		}
	}

	/**
	 * Overrides the language of the path,
	 * unless the language has been deleted
	 */
	public function testResolveLanguage(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.logs' => false
			],
			'urls' => [
				'index' => 'https://example.com'
			],
			'languages' => [
				['code' => 'de', 'default' => true],
				['code' => 'en']
			],
			'site' => [
				'children' => [
					[
						'slug'         => 'target',
						'translations' => [
							['code' => 'de', 'content' => ['uuid' => 'target']],
							['code' => 'en', 'slug' => 'target-en']
						]
					]
				]
			]
		]);

		Retour::instance($app);

		$cases = [
			['page://target', 'en', '/en/target-en'],
			['target-en', 'en', '/en/target-en'],
			['page://target', 'fr', '/de/target'],
			['page://target', null, '/de/target'],
		];

		foreach ($cases as [$to, $language, $expected]) {
			$app->setCurrentLanguage('de');

			$redirect = new Redirect(from: 'foo', to: $to, language: $language, status: 301);
			$this->assertSame('https://example.com' . $expected, $redirect->resolve([])->header('Location'), $to . ' in ' . $language);
		}

		// shows the page in the language
		$redirect = new Redirect(from: 'foo', to: 'target', language: 'en', status: 200);
		$this->assertInstanceOf(Page::class, $redirect->resolve([]));
		$this->assertSame('en', $app->language()->code());
	}

	public function testResolvePreserveQueryUnaffectedForNonRedirectStatus(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.logs'          => false,
				'distantnative.retour.preserveQuery' => true
			],
			'server' => [
				'REQUEST_URI' => '/foo?gclid=abc'
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'projects',
						'children' => [
							['slug' => 'project-a']
						]
					]
				]
			],
		]);

		Retour::instance($app);

		// status 200 never reaches the redirect branch, so the
		// preserveQuery option has no effect here
		$redirect = new Redirect(
			from: 'foo',
			to: 'projects/project-a',
			status: 200
		);

		$this->assertInstanceOf(Page::class, $redirect->resolve([]));
	}

	public function testResolveRedirect(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.logs' => false
			],
			'urls' => [
				'index' => 'https://example.com'
			],
			'site' => [
				'children' => [
					['slug' => 'home'],
					[
						'slug'     => 'projects',
						'children' => [
							['slug' => 'project-a']
						]
					]
				]
			],
		]);

		Retour::instance($app);

		$redirect = new Redirect(
			from: 'foo',
			to: 'projects/project-a',
			status: 301
		);

		$response = $redirect->resolve([]);
		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame(301, $response->code());
		$this->assertSame('https://example.com/projects/project-a', $response->header('Location'));

		// content representation of the page
		$redirect = new Redirect(
			from: 'foo.json',
			to: 'projects/project-a.json',
			status: 307
		);

		$response = $redirect->resolve([]);
		$this->assertSame(307, $response->code());
		$this->assertSame('https://example.com/projects/project-a.json', $response->header('Location'));

		// content representation of the home page
		$redirect = new Redirect(
			from: 'feed',
			to: 'home.rss',
			status: 301
		);

		$response = $redirect->resolve([]);
		$this->assertSame('https://example.com/home.rss', $response->header('Location'));
	}

	public function testResolveRedirectMailto(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.logs' => false
			]
		]);

		Retour::instance($app);

		$redirect = new Redirect(
			from: 'contact',
			to: 'mailto:info@example.com',
			status: 302
		);

		$response = $redirect->resolve([]);
		$this->assertSame('mailto:info@example.com', $response->header('Location'));
	}

	public function testResolveRedirectPreserveQuery(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.logs'          => false,
				'distantnative.retour.preserveQuery' => true
			],
			'urls' => [
				'index' => 'https://example.com'
			],
			'server' => [
				'REQUEST_URI' => '/foo?gclid=abc'
			],
			'site' => [
				'children' => [
					['slug' => 'projects']
				]
			],
		]);

		Retour::instance($app);

		$redirect = new Redirect(
			from: 'foo',
			to: 'projects',
			status: 302
		);

		$response = $redirect->resolve([]);
		$this->assertSame(302, $response->code());
		$this->assertSame('https://example.com/projects?gclid=abc', $response->header('Location'));
	}

	public function testResolveRedirectPreserveQueryUnchanged(): void
	{
		$query = '_gl=1*abc*_ga*MTIz&utm.source=x&flag&a%20b=c+d';
		$app   = $this->kirby->clone([
			'options' => [
				'distantnative.retour.logs'          => false,
				'distantnative.retour.preserveQuery' => true
			],
			'urls' => [
				'index' => 'https://example.com'
			],
			'server' => [
				'REQUEST_URI' => '/foo?' . $query
			]
		]);

		Retour::instance($app);

		$redirect = new Redirect(
			from: 'foo',
			to: 'https://exämple.com/target',
			status: 301
		);

		$response = $redirect->resolve([]);
		$this->assertSame('https://xn--exmple-cua.com/target?' . $query, $response->header('Location'));
	}

	public function testResolveRedirectWithoutPage(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.logs' => false
			],
			'urls' => [
				'index' => 'https://example.com/sub'
			]
		]);

		Retour::instance($app);

		$targets = [
			'downloads/file.pdf'              => 'https://example.com/sub/downloads/file.pdf',
			'/downloads/file.pdf'             => 'https://example.com/sub/downloads/file.pdf',
			''                                => 'https://example.com/sub',
			'https://getkirby.com/docs?a=1#b' => 'https://getkirby.com/docs?a=1#b',
		];

		foreach ($targets as $to => $expected) {
			$redirect = new Redirect(
				from: 'foo',
				to: $to,
				status: 301
			);

			$response = $redirect->resolve([]);
			$this->assertSame(301, $response->code(), $to);
			$this->assertSame($expected, $response->header('Location'), $to);
		}
	}

	public function testResolveRedirectWithoutPagePreserveQuery(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.logs'          => false,
				'distantnative.retour.preserveQuery' => true
			],
			'urls' => [
				'index' => 'https://example.com/sub'
			],
			'server' => [
				'REQUEST_URI' => '/sub/foo?gclid=abc'
			]
		]);

		Retour::instance($app);

		$redirect = new Redirect(
			from: 'foo',
			to: 'downloads/file.pdf',
			status: 302
		);

		$response = $redirect->resolve([]);
		$this->assertSame('https://example.com/sub/downloads/file.pdf?gclid=abc', $response->header('Location'));
	}

	public function testResolveWithoutPage(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.logs' => false
			]
		]);

		Retour::instance($app);

		$redirect = new Redirect(
			from: 'foo',
			to: 'does-not-exist',
			status: 410
		);

		$response = $redirect->resolve([]);
		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame(410, $response->code());
		$this->assertSame('', $response->body());
		$this->assertNull($response->header('Location'));
	}

	public function testResolveWithoutPageStatus200(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.logs' => false
			]
		]);

		Retour::instance($app);

		$redirect = new Redirect(
			from: 'foo',
			to: 'does-not-exist',
			status: 200
		);

		// handled as failure, which shows the error page
		$this->assertNull($redirect->resolve([]));
	}

	public function testRouteResolve(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.logs' => false
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'projects',
						'children' => [
							[
								'slug' => 'project-a'
							]
						]
					]
				]
			],
		]);

		Retour::instance($app);

		$redirect = new Redirect(
			from: 'foo',
			to: 'projects/project-a',
			status: 200
		);

		$route = $redirect->toRoute();
		$this->assertInstanceOf(Page::class, $route['action']());
	}

	public function testStatus(): void
	{
		$redirect = new Redirect(from: 'foo');
		$this->assertNull($redirect->status());

		$redirect = new Redirect(from: 'foo', status: 300);
		$this->assertSame(300, $redirect->status());
	}

	public function testToArray(): void
	{
		$expected = [
			'from'     => 'foo',
			'to'       => 'bar',
			'language' => 'en',
			'status'   => 301,
			'priority' => true,
			'comment'  => 'Old blog',
			'creator'  => 'user://homer',
			'modifier' => 'user://marge',
		];

		$this->assertSame($expected, (new Redirect(...$expected))->toArray());
		$this->assertSame($expected, Redirect::factory($expected)->toArray());

		// defaults
		$this->assertSame([
			'from'     => 'foo',
			'to'       => null,
			'language' => null,
			'status'   => null,
			'priority' => false,
			'comment'  => null,
			'creator'  => null,
			'modifier' => null,
		], (new Redirect(from: 'foo'))->toArray());
	}

	public function testToPath(): void
	{
		$path = Redirect::toPath('foo');
		$this->assertSame('foo', $path);

		$path = Redirect::toPath('/');
		$this->assertSame('home', $path);

		$path = Redirect::toPath('foo/$1/homer/$2', ['bar', 'simpson']);
		$this->assertSame('foo/bar/homer/simpson', $path);

		$route = new Route('', '', function () {
		});
		$args = $route->parse('foo/(:any)/homer/(:all)', 'foo/bar/homer/simpson');
		$path = Redirect::toPath('foo/$1/homer/$2', $args);
		$this->assertSame('foo/bar/homer/simpson', $path);
	}

	public function testToRoute(): void
	{
		$redirect = new Redirect(
			from: 'foo/(:any)',
			to: 'bar',
			status: 300
		);

		$route = $redirect->toRoute();

		$this->assertSame('foo/(:any)', $route['pattern']);
		$this->assertSame('GET|HEAD', $route['method']);
		$this->assertInstanceOf(Closure::class, $route['action']);
	}

	public function testWithQuery(): void
	{
		// request without query string: target is left untouched
		$app = $this->kirby->clone([
			'server' => [
				'REQUEST_URI' => '/foo'
			]
		]);

		Retour::instance($app);

		$to = Redirect::withQuery('/bar');
		$this->assertSame('/bar', $to);

		$app = $this->kirby->clone([
			'server' => [
				'REQUEST_URI' => '/foo?gclid=abc'
			]
		]);

		Retour::instance($app);

		// clean target: query string is appended with `?`
		$to = Redirect::withQuery('/bar');
		$this->assertSame('/bar?gclid=abc', $to);

		// target with its own query string: merged with `&`
		$to = Redirect::withQuery('/bar?foo=1');
		$this->assertSame('/bar?foo=1&gclid=abc', $to);

		// target with fragment: query string goes before it
		$to = Redirect::withQuery('/bar#section');
		$this->assertSame('/bar?gclid=abc#section', $to);

		$to = Redirect::withQuery('https://example.com/bar?foo=1#section');
		$this->assertSame('https://example.com/bar?foo=1&gclid=abc#section', $to);
	}

	public function testWithQueryUnchanged(): void
	{
		// query string is passed on as sent, not re-encoded
		$query = '_gl=1*abc*_ga*MTIz&utm.source=x&flag&a%20b=c+d&x[]=1';
		$app   = $this->kirby->clone([
			'server' => [
				'REQUEST_URI' => '/foo?' . $query
			]
		]);

		Retour::instance($app);

		$to = Redirect::withQuery('/bar');
		$this->assertSame('/bar?' . $query, $to);
	}
}
