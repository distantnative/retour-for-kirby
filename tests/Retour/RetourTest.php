<?php

namespace Kirby\Retour;


/**
 * @coversDefaultClass \Kirby\Retour\Retour
 */
class RetourTest extends TestCase
{
    /**
     * @covers ::hasLog
     */
    public function testHasLog(): void
    {
        $retour = Retour::instance();
        $this->assertTrue($retour->hasLog());

        // no matching ignore
        $app = $this->app(['distantnative.retour.logs' => false]);
        $retour = Retour::instance($app);
        $this->assertFalse($retour->hasLog());
    }

    /**
     * @covers ::ignore
     */
    public function testIgnore(): void
    {
        // no ignore
        $path   = 'foo/bar';
        $retour = Retour::instance();
        $this->assertFalse($retour->ignore($path));

        // no matching ignore
        $app = $this->app([
            'distantnative.retour.ignore' => [
                'foo',
                'bar'
            ]
        ]);
        $retour = Retour::instance($app);
        $this->assertFalse($retour->ignore($path));

        // direct matching string ignore
        $app = $this->app([
            'distantnative.retour.ignore' => [
                'foo/bar',
                'bar'
            ]
        ]);
        $retour = Retour::instance($app);
        $this->assertTrue($retour->ignore($path));

        // ignore with wildcard placeholder
        $app = $this->app([
            'distantnative.retour.ignore' => [
                'foo/(:any)',
                'bar'
            ]
        ]);
        $retour = Retour::instance($app);
        $this->assertTrue($retour->ignore($path));
    }
}
