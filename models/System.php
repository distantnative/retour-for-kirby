<?php

namespace distantnative\Retour;

use Kirby\Http\Header;

class System
{
    public function toArray(): array
    {
        $kirby  = kirby();
        $plugin = $kirby->plugin('distantnative/retour');

        return [
            'description' => $plugin->description(),
            'version'     => $plugin->version(),
            'site'        => $kirby->site()->url(),
            'view'        => option('distantnative.retour.view'),
            'limit'       => option('distantnative.retour.limit'),
            'headers'     => Header::$codes
        ];
    }
}
