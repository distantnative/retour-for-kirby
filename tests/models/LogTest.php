<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

class LogTest extends TestCase
{
    public function testRead(): void
    {
        Data::write(Log::$file, $data = ['homer' => 'simpson'], 'yaml');
        $this->assertEquals($data, Log::read());
        F::remove(Log::$file);
    }

    public function testReadDefaults(): void
    {
        $this->assertEquals([], Log::read());
    }


    public function testWrite(): void
    {
        $write = Log::write($data = ['homer' => 'simpson']);

        $this->assertTrue($write);
        $this->assertTrue(F::exists(Log::$file));
        $this->assertEquals($data, Log::read());
        $this->assertEquals($data, Data::read(Log::$file, 'yaml'));

        F::remove(Log::$file);
    }
}
