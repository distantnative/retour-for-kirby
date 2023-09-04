<?php

namespace Kirby\Retour;

/**
 * @coversNothing
 */
class RoutesTest extends TestCase
{
    public function testExtension(): void
    {
        $extension = require dirname(__DIR__, 2) . '/src/extensions/routes.php';

        $app = $this->kirby->clone([
            'options' => [
                'distantnative.retour.config' => __DIR__ . '/tmp/test.yml',
                'distantnative.retour.logs'   => false
            ]
        ]);

        $this->assertSame(0, count($extension()));

        $retour = Retour::instance($app);
        $retour->redirects()->prepend(new Redirect([
            'from' => 'foo',
            'to'   => 'bar'
        ]));

        $this->assertSame(0, count($extension()));

        $retour->redirects()->prepend(new Redirect([
            'from'     => 'foo',
            'to'       => 'bar',
            'priority' => true,
            'status'   => 307
        ]));

        $this->assertSame(1, count($extension()));
    }
}
