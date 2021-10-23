<?php

namespace distantnative\Retour;

use Kirby\Cms\App;

use PHPUnit\Framework\TestCase;
use RetourTestCase;

final class RoutesTest extends TestCase
{
    use RetourTestCase;

    public function testExtension(): void
    {
        $extension = require dirname(__DIR__, 2) . '/src/extensions/routes.php';

        $app = new App([
            'roots'   => ['index' => '/dev/null'],
            'options' => [
                'distantnative.retour.config' => __DIR__ . '/fixtures/test.yml',
                'distantnative.retour.logs'   => false
            ]
        ]);

        $this->assertSame(0, count($extension()));

        retour()->redirects()->prepend(new Redirect([
            'from' => 'foo',
            'to'   => 'bar'
        ]));

        $this->assertSame(0, count($extension()));

        retour()->redirects()->prepend(new Redirect([
            'from'     => 'foo',
            'to'       => 'bar',
            'priority' => true,
            'status'   => 307
        ]));

        $this->assertSame(1, count($extension()));
    }
}
