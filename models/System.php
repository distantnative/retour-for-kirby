<?php

namespace distantnative\Retour;

use Kirby\Http\Header;

class System
{

    public static function toArray(): array
    {
        $plugin = kirby()->plugin('distantnative/retour');

        return [
            'version'     => $plugin ? $plugin->version() : '-',
            'headers'     => Header::$codes
        ];
    }
}
