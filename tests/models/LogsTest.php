<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

class LogsTest extends TestCase
{

    public function testAdd(): void
    {
        $logs = new Logs;
        $logs->add($add = [
            [
                'path'     => $path = 'podcast/archive',
                'referrer' => $referrer = null,
                'status'   => 'failed',
                'date'     => '2019-01-29 23:45'
            ]
        ]);

        $this->assertTrue(F::exists(Logs::$file));

        $this->assertEquals(
            $add[0]['path'],
            Data::read(Logs::$file, 'yaml')[$path . '$' . $referrer]['path']
        );

        F::remove(Logs::$file);
    }

    public function testDataEmpty(): void
    {
        $logs = new Logs;
        $this->assertEquals([], $logs->data());
    }

    public function testData(): void
    {
        $logs = new Logs;
        $logs->write($data = ['homer' => 'simpson']);
        $this->assertEquals($data, $logs->data());
        F::remove(Logs::$file);
    }

    public function testFails(): void
    {
        $logs = new Logs;
        $logs->add([
            [
                'path'     => 'podcast/archive',
                'referrer' => null,
                'status'   => 'failed',
                'date'     => '2019-01-29 23:45'
            ],
            [
                'path'     => 'podcast/episode',
                'referrer' => null,
                'status'   => 'redirected',
                'date'     => '2019-01-29 23:45'
            ],
            [
                'path'     => 'podcast/main',
                'referrer' => null,
                'status'   => 'failed',
                'date'     => '2019-01-29 23:45'
            ]
        ]);

        $this->assertEquals(2, count($logs->fails()));

        F::remove(Logs::$file);
    }
}
