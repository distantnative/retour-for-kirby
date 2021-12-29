<?php

namespace distantnative\Retour;

use Kirby\Toolkit\F;

/**
 * @coversDefaultClass \distantnative\Retour\Config
 */
class ConfigTest extends TestCase
{
    /**
     * @covers ::set
     */
    public function tesSetBeforeLoad(): void
    {
        Config::$file = null;
        $this->expectException('Kirby\Exception\LogicException');
        Config::set('foo', 'bar');
    }
    /**
     * @covers ::file
     * @covers ::load
     */
    public function testFileCallable(): void
    {
        $file = __DIR__ . '/fixtures/callable.yml';
        $this->assertFalse(F::exists($file));

        Config::load(fn (): string => $file);
        Config::set('foo', 'bar');

        $this->assertTrue(F::exists($file));
        F::remove($file);
    }

    /**
     * @covers ::file
     * @covers ::load
     */
    public function testFileJSON(): void
    {
        $file = __DIR__ . '/fixtures/sample.json';
        Config::load($file);
        $this->assertSame(2, count(Config::$data['redirects']));
        $this->assertSame('about/team', Config::$data['redirects'][1]['to']);
    }

    /**
     * @covers ::load
     */
    public function testFileNotExists(): void
    {
        Config::load(__DIR__ . '/fixtures/nowhere.yml');
        $this->assertSame([], Config::$data);
    }

    /**
     * @covers ::set
     */
    public function testSet(): void
    {
        $file = __DIR__ . '/tmp/test.yml';
        Config::load($file);
        $this->assertFalse(F::exists($file));

        Config::set('foo', 'bar');
        $this->assertTrue(F::exists($file));
    }
}
