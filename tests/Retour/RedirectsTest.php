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
        $redirects = Plugin::instance()->redirects();
        $this->assertSame(0, $redirects->count());
        $redirects->create(['from' => 'foo']);
        $this->assertSame(1, $redirects->count());
        $this->assertSame('foo', $redirects->first()->from());
    }

    /**
     * @covers ::create
     */
    public function testCreateDuplicate(): void
    {
        $redirects = Plugin::instance()->redirects();

        $this->assertSame(0, $redirects->count());
        $redirects->create(['from' => 'foo']);
        $this->assertSame(1, $redirects->count());

        $this->expectException('Kirby\Exception\DuplicateException');
        $redirects->create(['from' => 'foo']);
    }

    /**
     * @covers ::create
     */
    public function testCreateFromQuery(): void
    {
        $_GET['from']     = $from     = 'from1';
        $_GET['to']       = $to       = 'to2';
        $_GET['status']   = $status   = 'status3';
        $_GET['priority'] = $priority = 'priority4';
        $_GET['comment']  = $comment  = 'comment5';

        $this->assertSame([
            'from'      => $from,
            'to'        => $to,
            'status'    => $status,
            'priority'  => $priority,
            'comment'   => $comment
        ], Redirects::fromQuery());

        unset(
            $_GET['from'],
            $_GET['to'],
            $_GET['status'],
            $_GET['priority'],
            $_GET['comment']
        );
    }

    /**
     * @covers ::create
     */
    public function testCreateWithoutValidParameters(): void
    {
        $redirects = Plugin::instance()->redirects();
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $redirects->create([]);
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
     * @covers ::fromQuery
     */
    public function testFromQuery(): void
    {
        $redirects = Plugin::instance()->redirects();
        $_GET['from'] = 'bar';
        $this->assertSame(0, $redirects->count());

        $redirects->create();
        $this->assertSame(1, $redirects->count());
        $this->assertSame('bar', $redirects->first()->from());
        unset($_GET['from']);
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
        $this->assertSame(0, count($routes));
    }

    /**
     * @covers ::update
     */
    public function testUpdate(): void
    {
        $redirects = Plugin::instance()->redirects();

        // creating new
        $this->assertSame(0, $redirects->count());
        $redirects->create(['from' => 'foo']);
        $this->assertSame(1, $redirects->count());
        $this->assertSame('foo', $redirects->first()->from());

        // updating existing
        $redirects->update('foo', ['from' => 'bar']);
        $this->assertSame(1, $redirects->count());
        $this->assertSame('bar', $redirects->first()->from());
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
