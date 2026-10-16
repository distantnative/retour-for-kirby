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
	public function testConstructValidation(): void
	{
		$this->expectExceptionMessage('Route requires path');
		$this->expectException(InvalidArgumentException::class);
		new Redirect([]);
	}

	public function testId(): void
	{
		$redirect = new Redirect(['from' => 'foo']);
		$this->assertSame('foo', $redirect->id());

		$redirect = new Redirect(['from' => 'foo/bar']);
		$this->assertSame('foobar', $redirect->id());
	}

	public function testIsActive(): void
	{
		$redirect = new Redirect(['from' => 'foo']);
		$this->assertFalse($redirect->isActive());

		$redirect = new Redirect(['from' => 'foo', 'status' => null]);
		$this->assertFalse($redirect->isActive());

		$redirect = new Redirect(['from' => 'foo', 'status' => 'disabled']);
		$this->assertFalse($redirect->isActive());

		$redirect = new Redirect(['from' => 'foo', 'status' => '507']);
		$this->assertTrue($redirect->isActive());
	}

	public function testPriority(): void
	{
		$redirect = new Redirect(['from' => 'foo']);
		$this->assertFalse($redirect->priority());

		$redirect = new Redirect(['from' => 'foo', 'priority' => false]);
		$this->assertFalse($redirect->priority());

		$redirect = new Redirect(['from' => 'foo', 'priority' => 'false']);
		$this->assertFalse($redirect->priority());

		$redirect = new Redirect(['from' => 'foo', 'priority' => null]);
		$this->assertFalse($redirect->priority());

		$redirect = new Redirect(['from' => 'foo', 'priority' => true]);
		$this->assertTrue($redirect->priority());

		$redirect = new Redirect(['from' => 'foo', 'priority' => 'true']);
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
		$redirect = new Redirect([
			'from'   => 'foo',
			'to'     => 'projects/project-a',
			'status' => 200
		]);

		$this->assertInstanceOf(Page::class, $redirect->resolve([]));

		// resolves with placeholder substitution
		$redirect = new Redirect([
			'from'   => 'old/(:any)',
			'to'     => 'projects/$1',
			'status' => 200
		]);

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

		$redirect = new Redirect([
			'from'   => 'old/(:all)',
			'to'     => '$1',
			'status' => 200
		]);

		$this->assertInstanceOf(Page::class, $redirect->resolve(['public']));

		// drafts and their children are treated as not found
		foreach (['secret', 'secret/child', 'page://secret'] as $path) {
			$response = $redirect->resolve([$path]);
			$this->assertInstanceOf(Response::class, $response, $path);
			$this->assertSame(200, $response->code(), $path);
			$this->assertSame('', $response->body(), $path);
		}
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
		$redirect = new Redirect([
			'from'   => 'foo',
			'to'     => 'projects/project-a',
			'status' => 200
		]);

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

		$redirect = new Redirect([
			'from'   => 'foo',
			'to'     => 'projects/project-a',
			'status' => 301
		]);

		$response = $redirect->resolve([]);
		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame(301, $response->code());
		$this->assertSame('https://example.com/projects/project-a', $response->header('Location'));

		// content representation of the page
		$redirect = new Redirect([
			'from'   => 'foo.json',
			'to'     => 'projects/project-a.json',
			'status' => 307
		]);

		$response = $redirect->resolve([]);
		$this->assertSame(307, $response->code());
		$this->assertSame('https://example.com/projects/project-a.json', $response->header('Location'));
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

		$redirect = new Redirect([
			'from'   => 'foo',
			'to'     => 'projects',
			'status' => 302
		]);

		$response = $redirect->resolve([]);
		$this->assertSame(302, $response->code());
		$this->assertSame('https://example.com/projects?gclid=abc', $response->header('Location'));
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
			$redirect = new Redirect([
				'from'   => 'foo',
				'to'     => $to,
				'status' => 301
			]);

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

		$redirect = new Redirect([
			'from'   => 'foo',
			'to'     => 'downloads/file.pdf',
			'status' => 302
		]);

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

		$redirect = new Redirect([
			'from'   => 'foo',
			'to'     => 'does-not-exist',
			'status' => 410
		]);

		$response = $redirect->resolve([]);
		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame(410, $response->code());
		$this->assertSame('', $response->body());
		$this->assertNull($response->header('Location'));
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

		$redirect = new Redirect([
			'from'   => 'foo',
			'to'     => 'projects/project-a',
			'status' => 200
		]);

		$route = $redirect->toRoute();
		$this->assertInstanceOf(Page::class, $route['action']());
	}

	public function testStatus(): void
	{
		$redirect = new Redirect(['from' => 'foo']);
		$this->assertSame(null, $redirect->status());

		$redirect = new Redirect(['from' => 'foo', 'status' => null]);
		$this->assertSame(null, $redirect->status());

		$redirect = new Redirect(['from' => 'foo', 'status' => 'disabled']);
		$this->assertSame(null, $redirect->status());

		$redirect = new Redirect(['from' => 'foo', 'status' => 300]);
		$this->assertSame(300, $redirect->status());
	}

	public function testToArray(): void
	{
		$redirect = new Redirect([
			'from'   => 'foo',
			'to'     => 'bar',
			'status' => 'disabled'
		]);

		$expected = [
			'from'     => 'foo',
			'to'       => 'bar',
			'status'   => null,
			'priority' => false,
			'comment'  => null,
			'creator'  => null,
			'modifier' => null,
		];

		$this->assertSame($expected, $redirect->toArray());
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
		$redirect = new Redirect([
			'from'   => 'foo/(:any)',
			'to'     => 'bar',
			'status' => 300
		]);

		$route = $redirect->toRoute();

		$this->assertSame('foo/(:any)', $route['pattern']);
		$this->assertSame('GET|HEAD', $route['method']);
		$this->assertInstanceOf(Closure::class, $route['action']);
	}

	public function testToRouteDisabled(): void
	{
		$redirect = new Redirect([
			'from'   => 'foo',
			'to'     => 'bar',
			'status' => 'disabled'
		]);

		$this->assertNull($redirect->toRoute());
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
