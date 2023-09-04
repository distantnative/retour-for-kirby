<?php

namespace Kirby\Retour;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;

/**
 * @coversDefaultClass \Kirby\Retour\Redirects
 */
class RedirectsTest extends TestCase
{
    /**
     * @covers ::create
     */
    public function testCreate(): void
    {
        $redirects = Retour::instance()->redirects();
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
        $redirects = Retour::instance()->redirects();

        $this->assertSame(0, $redirects->count());
        $redirects->create(['from' => 'foo']);
        $this->assertSame(1, $redirects->count());

        $this->expectException(DuplicateException::class);
        $redirects->create(['from' => 'foo']);
    }

    /**
     * @covers ::create
     */
    public function testCreateWithoutValidParameters(): void
    {
        $redirects = Retour::instance()->redirects();
        $this->expectException(InvalidArgumentException::class);
        $redirects->create([]);
    }

    /**
     * @covers ::__construct
     * @covers ::factory
     */
    public function testFactory(): void
    {
        $retour    = Retour::instance();
        $redirects = Redirects::factory($retour, $this->data());

        $this->assertSame(3, $redirects->count());
        $this->assertInstanceOf(Redirect::class, $redirects->first());
    }

    /**
     * @covers ::retour
     */
    public function testRetour(): void
    {
        $retour    = Retour::instance();
        $redirects = Redirects::factory($retour, []);
        $this->assertInstanceOf(Retour::class, $redirects->retour());
        $this->assertSame($retour, $redirects->retour());
    }

    /**
     * @covers ::save
     */
    public function testSave(): void
    {
        $file = __DIR__ . '/tmp/redirects.yml';
        $this->assertFalse(F::exists($file));


        $app = $this->kirby->clone([
            'options' => [
                'distantnative.retour.config' => $file
            ]
        ]);

        $retour    = Retour::instance($app);
        $data      = $this->data();
        $expected  = $this->expected();
        $redirects = Redirects::factory($retour, $data);
        $config    = $retour->config();

        $redirects->save();
        $this->assertTrue(F::exists($file));
        $config->read();
        $this->assertSame($expected, $config->data('redirects'));

        F::remove($file);
        $this->assertFalse(F::exists($file));

        $new = [
            'from'     => 'auckland',
            'to'       => 'squamish',
            'status'   => null,
            'priority' => false,
            'comment'  => null,
            'creator'  => null,
        ];
        $redirect = new Redirect($new);
        $redirects = $redirects->prepend($redirect);

        array_unshift($expected, $new);

        $redirects->save();
        $this->assertTrue(F::exists($file));
        $this->assertSame($expected, $config->data('redirects'));
    }

    /**
     * @covers ::toArray
     */
    public function testToArray(): void
    {
        $retour    = Retour::instance();
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

        $retour    = Retour::instance($app);
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

        $retour    = Retour::instance($app);
        $redirects = Redirects::factory($retour, $this->data());
        $this->assertSame(
            $this->expected(),
            $redirects->toData('2020-01-01', '2020-12-31')
        );
    }

    /**
     * @covers ::toRoutes
     */
    public function testToRoutes(): void
    {
        $retour    = Retour::instance();
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
        $redirects = Retour::instance()->redirects();

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
                'comment'  => null,
                'creator'  => null,
            ],
            [
                'from'     => 'homer',
                'to'       => 'simpson',
                'status'   => null,
                'priority' => true,
                'comment'  => null,
                'creator'  => null,
            ],
            [
                'from'     => 'berlin',
                'to'       => 'caracas',
                'status'   => 200,
                'priority' => false,
                'comment'  => null,
                'creator'  => null,
            ]
        ];
    }
}
