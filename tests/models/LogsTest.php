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

    public function testFlush(): void
    {
        $file = Logs::$dir . '/a.txt';
        F::write($file, 'test');
        $this->assertTrue(F::exists($file));

        $flush = Logs::flush();
        $this->assertTrue($flush);
        $this->assertFalse(F::exists($file));
    }



    public function testStoreFailed(): void
    {
        $path = 'podcast/archive/03';
        $file = Logs::$dir . '/.' . md5($path) . '.' . time() . '.tmp';

        Logs::store($path, 'failed');

        $this->assertTrue(F::exists($file));
        $this->assertEquals([
            'path'     => $path,
            'referrer' => null,
            'status'   => 'failed',
            'pattern'  => null,
            'date'     => date('Y-m-d H:i')
        ], Data::read($file, 'yaml'));

        F::remove($file);
    }

    public function testStore(): void
    {
        $path   = 'podcast/archive/03';
        $file   = Logs::$dir . '/.' . md5($path) . '.' . time() . '.tmp';

        Logs::store($path, 'redirected', $pattern = 'podcast/archive/(:any)');

        $this->assertTrue(F::exists($file));
        $this->assertEquals([
            'path'     => $path,
            'referrer' => null,
            'status'   => 'redirected',
            'pattern'  => $pattern,
            'date'     => date('Y-m-d H:i')
        ], Data::read($file, 'yaml'));

        F::remove($file);
    }

    public function testTemporaries(): void
    {
        Logs::store('podcast/archive', 'redirected');
        Logs::store('podcast/not-there', 'failes');

        $tmp = Logs::temporaries();

        $this->assertEquals(2, count($tmp));
    }
}
