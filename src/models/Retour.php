<?php

namespace distantnative\Retour;

use Kirby\Http\Header;

class Retour
{

    /**
     * Return system info
     *
     * @return array
     */
    public static function info(): array
    {
        $plugin = kirby()->plugin('distantnative/retour');

        return [
            'version'     => $plugin ? $plugin->version() : '-',
            'headers'     => Header::$codes,
            'deleteAfter' => option('distantnative.retour.deleteAfter', false)
        ];
    }
}
