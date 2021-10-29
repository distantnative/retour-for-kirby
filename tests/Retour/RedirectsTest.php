<?php

namespace distantnative\Retour;

use Kirby\Filesystem\F;

/**
 * @coversDefaultClass \distantnative\Retour\Redirects
 */
class RedirectsTest extends TestCase
{
    /**
     * @covers ::create
     */
    public function testCreate(): void
    {
        $retour = Plugin::instance();
        $_GET['from'] = 'foo';

        $this->assertSame(0, $retour->redirects()->count());
        Redirects::create();
        $this->assertSame(1, $retour->redirects()->count());
        $this->assertSame('foo', $retour->redirects()->first()->from());

        $_GET['from'] = 'bar';
        Redirects::create(0);
        $this->assertSame(1, $retour->redirects()->count());
        $this->assertSame('foo', $retour->redirects()->first()->from());
    }

    /**
     * @covers ::create
     */
    public function testCreateWithoutValidParameters(): void
    {
        $_GET['from'] = null;
        $retour       = Plugin::instance();
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        Redirects::create();
    }

    /**
     * @covers ::factory
     */
    public function testFactory(): void
    {
        $retour    = Plugin::instance();
        $redirects = Redirects::factory($retour, $this->data());

        $this->assertSame(3, $redirects->count());
        $this->assertInstanceOf('distantnative\Retour\Redirect', $redirects->first());
    }

    /**
     * @covers ::__construct
     * @covers ::plugin
     */
    public function testPlugin(): void
    {
        $retour    = Plugin::instance();
        $redirects = Redirects::factory($retour, []);
        $this->assertInstanceOf('distantnative\Retour\Plugin', $redirects->plugin());
    }

    /**
     * @covers ::save
     */
    public function testSave(): void
    {
        $file = __DIR__ . '/tmp/redirects.yml';
        Config::load($file);
        $this->assertFalse(F::exists($file));

        $data      = $this->data();
        $expected  = $this->expected();
        $retour    = Plugin::instance();
        $redirects = Redirects::factory($retour, $data);

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
    }

    /**
     * @covers ::toArray
     */
    public function testToArray(): void
    {
        $retour    = Plugin::instance();
        $redirects = Redirects::factory($retour, $this->data());
        $expected  = $this->expected();

        $this->assertSame($expected, $redirects->toArray());
    }

    /**
     * @covers ::toData
     */
    public function testToData(): void
    {
        $app = $this->kirby->clone([
            'options' => [
                'distantnative.retour.database' => __DIR__ . '/tmp/test.sqlite'
            ]
        ]);

        $retour    = Plugin::instance($app);
        $redirects = Redirects::factory($retour, $this->data());
        $data      = $redirects->toData('2020-01-01', '2020-12-31');
        $this->assertSame(3, count($data));
        $this->assertSame(0, $data[0]['hits']);

        $retour->log()->add([
            'path'     => 'foo',
            'date'     => $last = '2020-01-31 14:30:15',
            'redirect' => 'foo'
        ]);

        $data = $redirects->toData('2020-01-01', '2020-12-31');
        $this->assertSame(3, count($data));
        $this->assertSame(1, $data[0]['hits']);
        $this->assertSame($last, $data[0]['last']);
    }

    /**
     * @covers ::toData
     */
    public function testToDataNoLog(): void
    {
        $app = $this->kirby->clone([
            'options' => [
                'distantnative.retour.logs' => false
            ]
        ]);

        $retour    = Plugin::instance($app);
        $redirects = Redirects::factory($retour, $this->data());
        $this->assertSame($this->expected(), $redirects->toData('2020-01-01', '2020-12-31'));
    }

    /**
     * @covers ::toRoutes
     */
    public function testToRoutes(): void
    {
        $retour    = Plugin::instance();
        $redirects = Redirects::factory($retour, $this->data());

        // without priority
        $routes = $redirects->toRoutes(false);
        $this->assertSame(2, count($routes));
        $this->assertSame('foo', $routes[0]['pattern']);

        // with priority
        $routes = $redirects->toRoutes(true);
        $this->assertSame(1, count($routes));
    }
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
}
