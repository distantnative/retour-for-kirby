<?php

namespace distantnative\Retour;

use ReflectionProperty;

/**
 * @coversDefaultClass \distantnative\Retour\Plugin
 */
class PluginTest extends TestCase
{
    /**
     * @covers ::hasLog
     */
    public function testHasLog()
    {
        // default = true
        $retour = Plugin::instance();
        $this->assertTrue($retour->hasLog());

        // explicitly deactivated
        $app = $this->kirby->clone([
            'options' => [
                'distantnative.retour.logs' => false
            ]
        ]);
        $retour = Plugin::instance($app);
        $this->assertFalse($retour->hasLog());

        // explicitly activated
        $app = $this->kirby->clone([
            'options' => [
                'distantnative.retour.logs' => true
            ]
        ]);
        $retour = Plugin::instance($app);
        $this->assertTrue($retour->hasLog());
    }

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
     * @covers ::log
     */
    public function testLog(): void
    {
        $app = $this->kirby->clone([
            'options' => [
                'distantnative.retour.database' => __DIR__ . '/tmp/test.sqlite'
            ]
        ]);

        $log = Plugin::instance($app)->log();
        $this->assertInstanceOf('distantnative\Retour\Log', $log);

        // Repeated access, cached instance
        $this->assertInstanceOf('distantnative\Retour\Log', Plugin::instance($app)->log());
    }

    /**
     * @covers ::log
     */
    public function testLogDisabled(): void
    {
        $app = $this->kirby->clone([
            'options' => [
                'distantnative.retour.logs'     => false,
                'distantnative.retour.database' => __DIR__ . '/tmp/test.sqlite'
            ]
        ]);

        $log = Plugin::instance($app)->log();
        $this->assertInstanceOf('distantnative\Retour\LogDisabled', $log);
    }

    /**
     * @covers ::option
     */
    public function testOption()
    {
        $app = $this->kirby->clone([
            'options' => [
                'distantnative.retour.foo'   => 'bar'
            ]
        ]);

        $retour = Plugin::instance($app);
        $this->assertSame('bar', $retour->option('foo'));
        $this->assertSame('simpson', $retour->option('homer', 'simpson'));
    }

    /**
     * @covers ::__construct
     * @covers ::redirects
     */
    public function testRedirects(): void
    {
        $app = $this->kirby->clone([
            'options' => [
                'distantnative.retour.config' => __DIR__ . '/fixtures/sample.yml'
            ]
        ]);

        $retour = Plugin::instance($app);
        $redirects = $retour->redirects();
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
        $app = $this->kirby->clone();
        $retour = Plugin::instance($app);
        $redirects = $retour->redirects();
        $this->assertInstanceOf('distantnative\Retour\Redirects', $redirects);
        $this->assertSame([], $redirects->toArray());
    }
}
