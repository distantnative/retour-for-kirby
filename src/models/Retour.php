<?php

namespace distantnative\Retour;

use Kirby\Http\Header;

class Retour
{
    public static function info(): array
    {
        return [
            'headers'     => Header::$codes,
            'logging'     => option('retour.logging', true),
            'deleteAfter' => option('retour.deleteAfter', false)
        ];
    }
}
