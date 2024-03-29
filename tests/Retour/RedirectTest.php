<?php

namespace Kirby\Retour;

use Closure;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Route;

/**
 * @coversDefaultClass \Kirby\Retour\Redirect
 */
class RedirectTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testConstructValidation(): void
	{
		$this->expectExceptionMessage('Route requires path');
		$this->expectException(InvalidArgumentException::class);
		new Redirect([]);
	}

	/**
	 * @covers ::id
	 */
	public function testId(): void
	{
		$redirect = new Redirect(['from' => 'foo']);
		$this->assertSame('foo', $redirect->id());

		$redirect = new Redirect(['from' => 'foo/bar']);
		$this->assertSame('foobar', $redirect->id());
	}

	/**
	 * @covers ::isActive
	 */
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

	/**
	 * @covers ::priority
	 */
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

	/**
	 * @covers ::status
	 */
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

	/**
	 * @covers ::__construct
	 * @covers ::toArray
	 */
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
		];

		$this->assertSame($expected, $redirect->toArray());
	}

	/**
	 * @covers ::toPath
	 */
	public function testToPath(): void
	{
		$path = Redirect::toPath('foo');
		$this->assertSame('foo', $path);

		$path = Redirect::toPath('/');
		$this->assertSame('home', $path);

		$path = Redirect::toPath('foo/$1/homer/$2', ['bar', 'simpson']);
		$this->assertSame('foo/bar/homer/simpson', $path);

		$route = new Route('', '', function () {});
		$args = $route->parse('foo/(:any)/homer/(:all)', 'foo/bar/homer/simpson');
		$path = Redirect::toPath('foo/$1/homer/$2', $args);
		$this->assertSame('foo/bar/homer/simpson', $path);
	}

	/**
	 * @covers ::toRoute
	 */
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

	/**
	 * @covers ::toRoute
	 */
	public function testToRouteDisabled(): void
	{
		$redirect = new Redirect([
			'from'   => 'foo',
			'to'     => 'bar',
			'status' => 'disabled'
		]);

		$this->assertFalse($redirect->toRoute());
	}

	/**
	 * @covers ::toRoute
	 */
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
}
