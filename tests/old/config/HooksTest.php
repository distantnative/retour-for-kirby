<?php

namespace distantnative\Retour;

use Kirby\Cms\App;
use Kirby\Filesystem\F;
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
        $app = new App([
            'roots'   => ['index' => '/dev/null'],
            'options' => [
                'distantnative.retour.ignore' => ['foo']
            ]
        ]);

        $hook = self::$hook;
        $this->assertSame('', $hook(self::$route, 'foo', 'GET', '', true));
    }

    public function testNotFinal(): void
    {
        $hook = self::$hook;
        $this->assertNull($hook(self::$route, 'foo', 'GET', '', false));
    }

    public function testNotMatching(): void
    {
        $app = new App([
            'roots'   => ['index' => '/dev/null'],
            'options' => [
                'distantnative.retour.logs' => true,
                'distantnative.retour.database' => $file = __DIR__ . '/fixtures/test.sqlite'
            ]
        ]);

        retour()->redirects()->prepend(new Redirect([
            'from'   => 'homer',
            'to'     => 'simpson',
            'status' => 307
        ]));

        $this->assertSame(0, retour()->log()->table()->count());

        $hook = self::$hook;
        $this->assertNull($hook(self::$route, 'foo', 'GET', '', true));

        $this->assertSame(1, retour()->log()->table()->count());

        F::remove($file);
    }
}
