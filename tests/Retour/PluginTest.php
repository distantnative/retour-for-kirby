<?php

namespace distantnative\Retour;

use ReflectionProperty;

/**
 * @coversDefaultClass \distantnative\Retour\Plugin
 */
class PluginTest extends TestCase
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
     * @covers ::option
     */
    public function testOption()
    {
        // $this->assertSame($this->tmp . '/media/versions-export', $this->plugin->exportRoot());
        // $this->assertSame('https://example.com/media/versions-export', $this->plugin->exportUrl());
        // $this->assertSame(20, $this->plugin->option('autodelete.count'));
    }
}
