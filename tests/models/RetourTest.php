<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

class RetourTest extends TestCase
{

    protected static $fixture = '/plugins/retour/tests/fixtures/logs';

    public static function setUpBeforeClass(): void
    {
        Retour::$root = static::$fixture;
    }

    public function testFlush(): void
    {
        $file = $this->_file() . '/a.txt';
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

    // public function testTmp(): void
    // {
    //     $path   = 'podcast/archive/03';
    //     $file   = static::$fixture . '/' . md5($path) . '.' . time() . '.tmp';
    //     $retour = new Retour;
    //     Retour::$logs = '/plugins/retour/tests/fixtures/logs';

    //     $retour->tmp(
    //         $path,
    //         true,
    //         $pattern = 'podcast/archive/(:any)'
    //     );

    //     $this->assertTrue(F::exists($file));
    //     $this->assertEquals([
    //         'path'     => $path,
    //         'referrer' => null,
    //         'isFail'   => true,
    //         'pattern'  => $pattern,
    //         'date'     => date('Y-m-d H:i')
    //     ], Data::read($file, 'yaml'));

    //     F::remove($file);
    // }

}
