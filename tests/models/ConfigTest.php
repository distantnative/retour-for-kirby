<?php

namespace distantnative\Retour;

use Kirby\Exception\LogicException;
use Kirby\Toolkit\F;

use PHPUnit\Framework\TestCase;
use RetourTestCase;

/**
 * @coversDefaultClass \distantnative\Retour\Config
 */
final class ConfigTest extends TestCase
{
    use RetourTestCase;

    /**
     * @covers ::write
     */
    public function testWriteBeforeLoad(): void
    {
        $this->expectException(LogicException::class);
        Config::write();
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
        $this->assertSame('/ueber/team', Config::$data['redirects'][1]['to']);
    }

    /**
     * @covers ::write
     */
    public function testWriteNotExists(): void
    {
        $file = __DIR__ . '/fixtures/test.yml';
        Config::load($file);
        $this->assertFalse(F::exists($file));

        Config::write();
        $this->assertTrue(F::exists($file));
        F::remove($file);
    }

    /**
     * @covers ::set
     */
    public function testWriteWhenSet(): void
    {
        $file = __DIR__ . '/fixtures/test.yml';
        Config::load($file);
        $this->assertFalse(F::exists($file));

        Config::set(['routes' => [['path' => 'foo/bar']]]);
        $this->assertTrue(F::exists($file));
        F::remove($file);
    }
}
