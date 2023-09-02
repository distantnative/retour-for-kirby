<?php

namespace distantnative\Retour;

class DialogsTest extends TestCase
{
    public function testDeleteRedirectDialog(): void
    {
        $dialog = $this->dialog('retour.redirect.delete');
        $this->assertSame('retour/redirects/(:any)/delete', $dialog['pattern']);

        $redirects = Plugin::instance()->redirects();
        $this->assertSame(0, $redirects->count());
        $redirects->prepend(new Redirect(['from' => 'foo']));
        $this->assertSame(1, $redirects->count());

        $load = $dialog['load']('foo');
        $this->assertSame('k-remove-dialog', $load['component']);

        $submit = $dialog['submit']('foo');
        $this->assertSame(0, $redirects->count());
    }

    protected function dialog(string $key): array
    {
        $dialogs = require dirname(__DIR__, 2) . '/src/extensions/dialogs.php';
        return $dialogs[$key];
    }
}
