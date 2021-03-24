<?php

namespace distantnative\Retour;

use Kirby\Cms\App;
use Kirby\Http\Route;

use PHPUnit\Framework\TestCase;
use RetourTestCase;

final class HooksTest extends TestCase
{
    use RetourTestCase;

    private static $hook;
    private static $route;

    public static function setUpBeforeClass(): void
    {
        $extension = require dirname(__DIR__, 2) . '/src/extensions/hooks.php';
        self::$hook = $extension['route:after'];
        self::$route = new Route('foo', 'GET', function () {
        });
    }

    public function testNotFinal(): void
    {
        $hook = self::$hook;
        $this->assertNull($hook(self::$route, 'foo', 'GET', '', false));
    }

    public function testIgnore(): void
    {
        $app = new App([
            'roots'   => ['index' => '/dev/null'],
            'options' => [
                'distantnative.retour.ignore' => ['foo']
            ]
        ]);

        $hook = self::$hook;
        $this->assertSame('', $hook(self::$route, 'foo', 'GET', '', true));
    }

    public function testNotMatching(): void
    {
        $app = new App([
            'roots'   => ['index' => '/dev/null'],
            'options' => [
                'distantnative.retour.logs' => false
            ]
        ]);

        retour()->redirects()->prepend(new Redirect([
            'from'   => 'homer',
            'to'     => 'simpson',
            'status' => 307
        ]));

        $hook = self::$hook;
        $this->assertNull($hook(self::$route, 'foo', 'GET', '', true));
    }

    // public function testMatching(): void
    // {
    //     $app = new App([
    //         'roots'   => ['index' => '/dev/null'],
    //         'options' => [
    //             'distantnative.retour.logs' => false
    //         ]
    //     ]);

    //     retour()->redirects()->prepend(new Redirect([
    //         'from'   => 'foo',
    //         'to'     => 'bar',
    //         'status' => 307
    //     ]));

    //     $hook = self::$hook;
    //     $this->assertNotNull($hook(self::$route, 'foo', 'GET', '', true));
    // }
}
