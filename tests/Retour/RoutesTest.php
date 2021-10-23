<?php

namespace distantnative\Retour;

class RoutesTest extends TestCase
{
    public function testExtension(): void
    {
        $extension = require dirname(__DIR__, 2) . '/src/config/routes.php';

        $app = $this->kirby->clone([
            'options' => [
                'distantnative.retour.config' => __DIR__ . '/fixtures/test.yml',
                'distantnative.retour.logs'   => false
            ]
        ]);

        $this->assertSame(0, count($extension()));

        $retour = Plugin::instance($app);
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
