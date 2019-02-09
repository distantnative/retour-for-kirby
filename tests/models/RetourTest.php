<?php

namespace distantnative;

use PHPUnit\Framework\TestCase;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

class RetourTest extends TestCase
{

    protected static $fixture = __DIR__ .'/../fixtures/logs';

    public function testClass(): void
    {
        $retour = new Retour;
        $this->assertInstanceOf('distantnative\Retour', $retour);
    }

    public function testFlush(): void
    {
        $file = static::$fixture . '/a.txt';
        F::write($file, 'test');

        $retour = new Retour;
        Retour::$logs = '/plugins/retour/tests/fixtures/logs';
        $this->assertTrue(F::exists($file));

        $retour->flush();
        $this->assertFalse(F::exists($file));
    }

    public function testLog(): void
    {
        $retour = new Retour;
        $this->assertInstanceOf('distantnative\Retour\Log', $retour->log());
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

    public function testTmp(): void
    {
        $path   = 'podcast/archive/03';
        $file   = static::$fixture . '/' . md5($path) . '.' . time() . '.tmp';
        $retour = new Retour;
        Retour::$logs = '/plugins/retour/tests/fixtures/logs';

        $retour->tmp(
            $path,
            true,
            $pattern = 'podcast/archive/(:any)'
        );

        $this->assertTrue(F::exists($file));
        $this->assertEquals([
            'path'     => $path,
            'referrer' => null,
            'isFail'   => true,
            'pattern'  => $pattern,
            'date'     => date('Y-m-d H:i')
        ], Data::read($file, 'yaml'));

        F::remove($file);
    }

}
