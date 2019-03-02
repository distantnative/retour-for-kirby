<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

class LogsTest extends TestCase
{
    public function testAdd(): void
    {
        $add = Logs::add($data = [
            [
                'path'     => $path = 'podcast/archive',
                'referrer' => $referrer = null,
                'status'   => 'failed',
                'date'     => '2019-01-29 23:45'
            ]
        ]);

        $this->assertTrue($add);
        $this->assertTrue(F::exists(Logs::$file));

        $this->assertEquals(
            $data[0]['path'],
            Data::read(Logs::$file, 'yaml')[$path . '$' . $referrer]['path']
        );

        F::remove(Logs::$file);
    }

    public function testReadEmpty(): void
    {
        $this->assertEquals([], Logs::read());
    }

    public function testRead(): void
    {
        Logs::write($data = ['homer' => 'simpson']);
        $this->assertEquals($data, Logs::read());
        F::remove(Logs::$file);
    }
}
