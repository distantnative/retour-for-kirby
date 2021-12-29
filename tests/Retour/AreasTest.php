<?php

namespace distantnative\Retour;

class AreasTest extends TestCase
{
    public function testExtension(): void
    {
        $area = $this->area();
        $this->assertSame('road-sign', $area['icon']);
        $this->assertIsArray($area['views']);
        $this->assertIsArray($area['dialogs']);
    }

    public function testShortcutView(): void
    {
        $area = $this->area();
        $this->expectException('Kirby\Panel\Redirect');
        $area['views'][0]['action']();
    }

    public function testView(): void
    {
        $area = $this->area();
        $view = $area['views'][1]['action']('redirects');
        $this->assertSame('k-retour-view', $view['component']);
    }

    protected function area(): array
    {
        $extension = require dirname(__DIR__, 2) . '/src/extensions/areas.php';
        return $extension['retour'](kirby());
    }
}
