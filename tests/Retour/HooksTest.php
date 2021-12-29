<?php

namespace distantnative\Retour;

use Kirby\Http\Route;

class HooksTest extends TestCase
{
    private static $hook;
    private static $route;

    public static function setUpBeforeClass(): void
    {
        $extension = require dirname(__DIR__, 2) . '/src/extensions/hooks.php';
        self::$hook = $extension['route:after'] ?? null;
        self::$route = new Route('foo', 'GET', function () {
        });
    }

    public function testHasRouteAfterHook(): void
    {
        $this->assertNotNull(self::$hook);
    }

    public function testIgnore(): void
    {
        $app = $this->kirby->clone([
            'options' => [
                'distantnative.retour.ignore' => ['foo']
            ]
        ]);

        $hook = self::$hook;
        $this->assertSame(null, $hook(self::$route, 'foo', 'GET', '', true));
    }

    public function testNotFinal(): void
    {
        $hook = self::$hook;
        $this->assertNull($hook(self::$route, 'foo', 'GET', '', false));
    }

    public function testNotMatching(): void
    {
        $app = $this->kirby->clone([
            'options' => [
                'distantnative.retour.logs' => true,
                'distantnative.retour.database' => $file = __DIR__ . '/tmp/test.sqlite'
            ]
        ]);

        $retour = Plugin::instance($app);

        $retour->redirects()->prepend(new Redirect([
            'from'   => 'homer',
            'to'     => 'simpson',
            'status' => 307
        ]));

        $this->assertSame(0, $retour->log()->table()->count());

        $hook = self::$hook;
        $this->assertNull($hook(self::$route, 'foo', 'GET', '', true));

        $this->assertSame(1, $retour->log()->table()->count());
    }
}
