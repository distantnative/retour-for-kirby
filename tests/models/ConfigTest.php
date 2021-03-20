<?php

namespace distantnative\Retour;

use Kirby\Exception\LogicException;
use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    protected function setUp(): void
    {
        Config::$data = [];
    }

    public function testWriteBeforeLoad(): void
    {
        $this->expectException(LogicException::class);
        Config::write();
    }

    public function testFileNotExists(): void
    {
        Config::load(__DIR__ . '/fixtures/nowhere.yml');
        $this->assertSame([], Config::$data);
    }

    public function testWriteNotExists(): void
    {
        $file = __DIR__ . '/fixtures/test.yml';
        Config::load($file);
        $this->assertFalse(F::exists($file));

        Config::write();
        $this->assertTrue(F::exists($file));
        F::remove($file);
    }

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
