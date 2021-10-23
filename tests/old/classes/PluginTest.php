<?php

namespace distantnative\Retour;

use Kirby\Cms\App;
use Kirby\Filesystem\F;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \distantnative\Retour\Plugin
 */
final class PluginTest extends TestCase
{
    /**
     * @backupStaticAttributes enabled
     * @covers ::instance
     * @covers ::__construct
     * @covers ::kirby
     */
    public function testInstance()
    {
        $property = new ReflectionProperty('distantnative\Retour\Plugin', 'instance');
        $property->setAccessible(true);
        $property->setValue(null);

        $kirby = $this->kirby->clone();

        $this->assertSame($this->kirby, $this->plugin->kirby());
        $this->assertNotSame($kirby, $this->plugin->kirby());

        $plugin = Plugin::instance($kirby);
        $this->assertSame($kirby, $plugin->kirby());

        $plugin2 = Plugin::instance();
        $this->assertSame($plugin, $plugin2);

        $plugin3 = Plugin::instance($this->kirby);
        $this->assertNotSame($plugin, $plugin3);
        $this->assertSame($this->kirby, $plugin3->kirby());

        $plugin4 = new Plugin($kirby);
        $this->assertSame($kirby, $plugin4->kirby());
    }

    /**
     * @covers ::__construct
     * @covers ::log
     */
    public function testLog(): void
    {
        $fixture = __DIR__ . '/fixtures/test.sqlite';

        $app = new App([
            'roots'   => ['index' => '/dev/null'],
            'options' => [
                'distantnative.retour.database' => $fixture
            ]
        ]);

        $log = retour()->log();
        $this->assertInstanceOf('distantnative\Retour\Log', $log);
        $this->assertInstanceOf('distantnative\Retour\Log', retour()->log());

        F::remove($fixture);

        Retour::$instance = null;
        $app = new App([
            'roots'   => ['index' => '/dev/null'],
            'options' => [
                'distantnative.retour.logs' => false,
                'distantnative.retour.database' => $fixture
            ]
        ]);
        $log = retour()->log();
        $this->assertInstanceOf('distantnative\Retour\LogDisabled', $log);

        F::remove($fixture);
    }

    /**
     * @covers ::meta
     */
    public function testMeta(): void
    {
        $app = new App([
            'roots'   => ['index' => '/dev/null'],
            'options' => [
                'distantnative.retour.logs' => false,
                'distantnative.retour.deleteAfter' => 6
            ]
        ]);

        $meta = Retour::meta();
        $this->assertSame(false, $meta['hasLog']);
        $this->assertSame(6, $meta['deleteAfter']);
        $this->assertTrue(count($meta['headers']) > 0);
    }

    /**
     * @covers ::meta
     */
    public function testMetaDefaults(): void
    {
        $app = new App([
            'roots'   => ['index' => '/dev/null']
        ]);

        $meta = Retour::meta();
        $this->assertSame(true, $meta['hasLog']);
        $this->assertSame(false, $meta['deleteAfter']);
        $this->assertTrue(count($meta['headers']) > 0);
    }

    /**
     * @covers ::__construct
     * @covers ::redirects
     */
    public function testRedirects(): void
    {
        $app = new App([
            'roots'   => ['index' => '/dev/null'],
            'options' => [
                'distantnative.retour.config' => __DIR__ . '/fixtures/sample.yml'
            ]
        ]);

        $redirects = retour()->redirects();
        $this->assertInstanceOf('distantnative\Retour\Redirects', $redirects);
        $this->assertSame(4, $redirects->count());
        $this->assertSame(4, count(Config::$data['redirects']));
    }

    /**
     * @covers ::__construct
     * @covers ::redirects
     */
    public function testRedirectsEmpty(): void
    {
        $app = new App([
            'roots'   => ['index' => '/dev/null']
        ]);

        $redirects = retour()->redirects();
        $this->assertInstanceOf('distantnative\Retour\Redirects', $redirects);
        $this->assertSame([], $redirects->toArray());
    }
}
