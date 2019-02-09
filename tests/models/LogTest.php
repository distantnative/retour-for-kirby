<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

class LogTest extends TestCase
{

    protected static $fixture = '/plugins/retour/tests/fixtures/retour.data';

    public static function setUpBeforeClass(): void
    {
        Log::$file = static::$fixture;
    }

    public function testFile(): void
    {
        $log = new Log;
        $this->assertEquals($this->_file(), $log->file());
    }

    public function testRead(): void
    {
        Data::write($this->_file(), $data = ['homer' => 'simpson'], 'yaml');

        $log = new Log;
        $this->assertEquals($data, $log->read());

        F::remove($this->_file());
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
        $this->assertTrue(F::exists($this->_file()));
        $this->assertEquals($data, Data::read($this->_file(), 'yaml'));

        F::remove($this->_file());
    }


    public function testData(): void
    {
        Data::write($this->_file(), $data = ['homer' => 'simpson'], 'yaml');

        $log = new Log;
        $this->assertEquals($data, $log->data());

        F::remove($this->_file());
    }
}
