<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

class RetourTest extends TestCase
{
    public function testFlush(): void
    {
        $file = Retour::$dir . '/a.txt';
        F::write($file, 'test');
        $this->assertTrue(F::exists($file));

        $flush = Retour::flush();
        $this->assertTrue($flush);
        $this->assertFalse(F::exists($file));
    }



    public function testStoreFailed(): void
    {
        $path = 'podcast/archive/03';
        $file = Retour::$dir . '/.' . md5($path) . '.' . time() . '.tmp';

        Retour::store($path, 'failed');

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
        $file   = Retour::$dir . '/.' . md5($path) . '.' . time() . '.tmp';

        Retour::store($path, 'redirected', $pattern = 'podcast/archive/(:any)');

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
        Retour::store('podcast/archive', 'redirected');
        Retour::store('podcast/not-there', 'failes');

        $tmp = Retour::temporaries();

        $this->assertEquals(2, count($tmp));
    }
}
