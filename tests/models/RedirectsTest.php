<?php

namespace distantnative\Retour;

use Kirby\Cms\App;
use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

final class RedirectsTest extends TestCase
{
    protected function data(): array
    {
        return [
            ['from' => 'foo', 'to' => 'bar', 'status' => 307],
            ['from' => 'homer', 'to' => 'simpson', 'status' => 'disabled', 'priority' => true],
            ['from' => 'berlin', 'to' => 'caracas', 'status' => 200]
        ];
    }

    protected function expected(): array
    {
        return [
            [
                'from'     => 'foo',
                'to'       => 'bar',
                'status'   => 307,
                'priority' => false,
                'comment'  => null
            ],
            [
                'from'     => 'homer',
                'to'       => 'simpson',
                'status'   => null,
                'priority' => true,
                'comment'  => null
            ],
            [
                'from'     => 'berlin',
                'to'       => 'caracas',
                'status'   => 200,
                'priority' => false,
                'comment'  => null
            ]
            ];
    }

    public function testFactory(): void
    {
        $redirects = Redirects::factory($this->data());

        $this->assertSame(3, $redirects->count());
        $this->assertInstanceOf('distantnative\Retour\Redirect', $redirects->first());
    }

    public function testSave(): void
    {
        $file = __DIR__ . '/fixtures/redirects.yml';
        Config::load($file);
        $this->assertFalse(F::exists($file));

        $data = $this->data();
        $expected = $this->expected();
        $redirects = Redirects::factory($data);

        $redirects->save();
        $this->assertTrue(F::exists($file));
        $this->assertSame($expected, Config::$data['redirects']);

        F::remove($file);
        $this->assertFalse(F::exists($file));

        $new = [
            'from'     => 'auckland',
            'to'       => 'squamish',
            'status'   => null,
            'priority' => false,
            'comment'  => null
        ];
        $redirect = new Redirect($new);
        $redirects = $redirects->prepend($redirect);

        array_unshift($expected, $new);

        $redirects->save();
        $this->assertTrue(F::exists($file));
        $this->assertSame($expected, Config::$data['redirects']);

        F::remove($file);
    }

    public function testToArray(): void
    {
        $redirects = Redirects::factory($this->data());
        $expected  = $this->expected();

        $this->assertSame($expected, $redirects->toArray());
    }

    public function testToDataNoLog(): void
    {
        $app = new App([
            'roots'   => ['index' => '/dev/null'],
            'options' => [
                'distantnative.retour.logs' => false
            ]
        ]);

        $redirects = Redirects::factory($this->data());
        $this->assertSame($this->expected(), $redirects->toData('2020-01-01', '2020-12-31'));
    }

    public function testToRoutes(): void
    {
        $redirects = Redirects::factory($this->data());

        // without priority
        $routes    = $redirects->toRoutes();
        $this->assertSame(2, count($routes));
        $this->assertSame('foo', $routes[0]['pattern']);

        // with priority
        $routes    = $redirects->toRoutes(true);
        $this->assertSame(0, count($routes));
    }

}
