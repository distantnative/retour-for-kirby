<?php

namespace distantnative\Retour;

use PHPUnit\Framework\TestCase;
use RetourTestCase;

final class TranslationsTest extends TestCase
{
    use RetourTestCase;

    public function testExtension(): void
    {
        $extension = require dirname(__DIR__, 2) . '/src/extensions/translations.php';

        $this->assertTrue(isset($extension['en']));
        $this->assertTrue(isset($extension['de']));
        $this->assertTrue(isset($extension['es_ES']));
        $this->assertTrue(isset($extension['fr']));
        $this->assertTrue(isset($extension['pt_BR']));
        $this->assertTrue(isset($extension['tr']));
    }
}
