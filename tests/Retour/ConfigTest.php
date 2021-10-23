<?php

namespace distantnative\Retour;

use Kirby\Toolkit\F;

/**
 * @coversDefaultClass \distantnative\Retour\Config
 */
class ConfigTest extends TestCase
{
    /**
     * @covers ::load
     */
    public function testFileCallable(): void
    {
        $file = __DIR__ . '/fixtures/callable.yml';
        $this->assertFalse(F::exists($file));

        $callable = function () use ($file): string {
            return $file;
        };

        Config::load($callable);
        Config::write();

        $this->assertTrue(F::exists($file));
        F::remove($file);
    }

    /**
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
     * @covers ::write
     */
    public function testWriteBeforeLoad(): void
    {
        Config::$file = null;
        $this->expectException('Kirby\Exception\LogicException');
        Config::write();
    }

    /**
     * @covers ::write
     */
    public function testWriteNotExists(): void
    {
        $file = __DIR__ . '/tmp/test.yml';
        Config::load($file);
        $this->assertFalse(F::exists($file));

        Config::write();
        $this->assertTrue(F::exists($file));
    }

    /**
     * @covers ::set
     */
    public function testWriteWhenSet(): void
    {
        $file = __DIR__ . '/tmp/test.yml';
        Config::load($file);
        $this->assertFalse(F::exists($file));

        Config::set(['routes' => [['path' => 'foo/bar']]]);
        $this->assertTrue(F::exists($file));
    }
}
