<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

class LogTest extends TestCase
{
    public function testRead(): void
    {
        Data::write(Log::$file, $data = ['homer' => 'simpson'], 'yaml');

        $log = new Log;
        $this->assertEquals($data, $log->read());

        F::remove(Log::$file);
    }

    public function testReadDefaults(): void
    {
        $log = new Log;
        $this->assertEquals([], $log->read());
    }


    public function testWrite(): void
    {
        $log   = new Log;
        $write = $log->write($data = ['homer' => 'simpson']);

        $this->assertEquals($data, $write);
        $this->assertTrue(F::exists(Log::$file));
        $this->assertEquals($data, Data::read(Log::$file, 'yaml'));

        F::remove(Log::$file);
    }


    public function testData(): void
    {
        Data::write(Log::$file, $data = ['homer' => 'simpson'], 'yaml');

        $log = new Log;
        $this->assertEquals($data, $log->data());

        F::remove(Log::$file);
    }
}
