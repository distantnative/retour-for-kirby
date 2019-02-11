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

        $retour = new Retour;
        $this->assertTrue(F::exists($file));

        $retour->flush();
        $this->assertFalse(F::exists($file));
    }

    public function testLogs(): void
    {
        $retour = new Retour;
        $this->assertInstanceOf('distantnative\Retour\Log', $retour->logs());
    }

    public function testRedirects(): void
    {
        $retour = new Retour;
        $this->assertInstanceOf('distantnative\Retour\Redirects', $retour->redirects());
    }

    public function testStats(): void
    {
        $retour = new Retour;
        $this->assertInstanceOf('distantnative\Retour\Stats', $retour->stats());
    }

    public function testSystem(): void
    {
        $retour = new Retour;
        $this->assertInstanceOf('distantnative\Retour\System', $retour->system());
    }

    public function testStoreFailed(): void
    {
        $retour = new Retour;
        $path   = 'podcast/archive/03';
        $file   = Retour::$dir . '/.' . md5($path) . '.' . time() . '.tmp';

        $retour->store($path, 'failed');

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
        $retour = new Retour;
        $path   = 'podcast/archive/03';
        $file   = Retour::$dir . '/.' . md5($path) . '.' . time() . '.tmp';

        $retour->store($path, 'redirected', $pattern = 'podcast/archive/(:any)');

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
        $retour = new Retour;

        $retour->store('podcast/archive', 'redirected');
        $retour->store('podcast/not-there', 'failes');

        $tmp = $retour->temporaries();

        $this->assertEquals(2, count($tmp));
    }
}
