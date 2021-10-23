<?php

namespace distantnative\Retour;

class TranslationsTest extends TestCase
{
    public function testExtension(): void
    {
        $extension = require dirname(__DIR__, 2) . '/src/config/i18n.php';

        $this->assertTrue(isset($extension['en']));
        $this->assertTrue(isset($extension['de']));
        $this->assertTrue(isset($extension['es_ES']));
        $this->assertTrue(isset($extension['fr']));
        $this->assertTrue(isset($extension['pt_BR']));
        $this->assertTrue(isset($extension['tr']));
    }
}
