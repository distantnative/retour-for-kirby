<?php

namespace Kirby\Retour;

use Closure;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
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
