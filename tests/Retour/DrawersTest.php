<?php

namespace distantnative\Retour;

class DrawersTest extends TestCase
{
    public function testCreateRedirectDrawer(): void
    {
        $drawer = $this->drawer('retour.redirect.create');
        $this->assertSame('retour/redirects/create', $drawer['pattern']);

        $load = $drawer['load']();
        $this->assertSame('k-form-drawer', $load['component']);
        $this->assertIsArray($load['props']['fields']);
        $this->assertArrayNotHasKey('value', $load['props']);

        $_GET['from'] = 'foo';
        $redirects = Plugin::instance()->redirects();
        $this->assertSame(0, $redirects->count());
        $submit = $drawer['submit']();
        $this->assertSame(1, $redirects->count());
        unset($_GET['from']);
    }

    public function testEditRedirectDrawer(): void
    {
        $_GET['from'] = 'bar';
        $drawer = $this->drawer('retour.redirect.edit');
        $this->assertSame('retour/redirects/(:any)/edit', $drawer['pattern']);

        $redirects = Plugin::instance()->redirects();
        $this->assertSame(0, $redirects->count());
        $redirects->prepend(new Redirect(['from' => 'foo']));
        $this->assertSame(1, $redirects->count());
        $this->assertSame('foo', $redirects->first()->from());

        $load = $drawer['load']('foo');
        $this->assertSame('k-form-drawer', $load['component']);
        $this->assertIsArray($load['props']['fields']);
        $this->assertIsArray($load['props']['value']);
        $this->assertSame('foo', $load['props']['value']['from']);

        $submit = $drawer['submit']('foo');
        $this->assertSame(1, $redirects->count());
        $this->assertSame('bar', $redirects->first()->from());
        unset($_GET['from']);
    }

    protected function drawer(string $key): array
    {
        $drawers = require dirname(__DIR__, 2) . '/src/extensions/drawers.php';
        return $drawers[$key];
    }
}
