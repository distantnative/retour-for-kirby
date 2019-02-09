<?php

namespace distantnative\Retour;

use PHPUnit\Framework\TestCase;
use Kirby\Data\Data;
use Kirby\Toolkit\F;

class StoreTest extends TestCase
{

    protected static $fixture = __DIR__ .'/../fixtures/retour.yml';

    protected function setUp(): void
    {
        Store::$file = static::$fixture;
    }

    public function testClass(): void
    {
        $store = new Store;
        $this->assertInstanceOf('distantnative\Retour\Store', $store);
    }

    public function testFile(): void
    {
        $store = new Store;
        $this->assertEquals(static::$fixture, $store->file());
    }

    public function testRead(): void
    {
        Data::write(static::$fixture, $data = ['homer' => 'simpson']);

        $store = new Store;
        $this->assertEquals($data, $store->read());

        F::remove(static::$fixture);
    }

    public function testReadDefaults(): void
    {
        $store = new Store;
        $this->assertEquals([], $store->read());
    }


    public function testWrite(): void
    {
        $store = new Store;
        $write = $store->write($data = ['homer' => 'simpson']);

        $this->assertEquals($data, $write);
        $this->assertTrue(F::exists(static::$fixture));
        $this->assertEquals($data, Data::read(static::$fixture));

        F::remove(static::$fixture);
    }


    public function testData(): void
    {
        Data::write(static::$fixture, $data = ['homer' => 'simpson']);

        $store = new Store;
        $this->assertEquals($data, $store->data());

        F::remove(static::$fixture);
    }

    public function testDataCached(): void
    {
        Data::write(static::$fixture, $data = ['homer' => 'simpson']);

        $store = new Store;
        $this->assertEquals($data, $store->data());
        $this->assertEquals($data, $store->data());

        F::remove(static::$fixture);
    }

}
