<?php

namespace distantnative\Retour;

use Kirby\Http\Route;
use PHPUnit\Framework\TestCase;

final class RedirectTest extends TestCase
{
    public function testPriority(): void
    {
        $redirect = new Redirect([]);
        $this->assertFalse($redirect->priority());

        $redirect = new Redirect(['priority' => false]);
        $this->assertFalse($redirect->priority());

        $redirect = new Redirect(['priority' => 'false']);
        $this->assertFalse($redirect->priority());

        $redirect = new Redirect(['priority' => null]);
        $this->assertFalse($redirect->priority());

        $redirect = new Redirect(['priority' => true]);
        $this->assertTrue($redirect->priority());

        $redirect = new Redirect(['priority' => 'true']);
        $this->assertTrue($redirect->priority());
    }

    public function testStatus(): void
    {
        $redirect = new Redirect([]);
        $this->assertSame(null, $redirect->status());
        $this->assertFalse($redirect->isActive());

        $redirect = new Redirect(['status' => null]);
        $this->assertSame(null, $redirect->status());
        $this->assertFalse($redirect->isActive());

        $redirect = new Redirect(['status' => 'disabled']);
        $this->assertSame(null, $redirect->status());
        $this->assertFalse($redirect->isActive());

        $redirect = new Redirect(['status' => 300]);
        $this->assertSame(300, $redirect->status());
        $this->assertTrue($redirect->isActive());
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
            'comment'  => null
        ];

        $this->assertSame($expected, $redirect->toArray());
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
        $this->assertInstanceOf('Closure', $route['action']);
    }

    public function testToRouteDisabled(): void
    {
        $redirect = new Redirect([
            'from'   => 'foo',
            'to'     => 'bar',
            'status' => 'disabled'
        ]);

        $this->assertFalse($redirect->toRoute());
    }
}
