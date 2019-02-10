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
            'version'     => $plugin->version(),
            'description' => $plugin->description(),
            'site'        => $kirby->site()->url(),
            'limit'       => option('distantnative.retour.limit'),
            'headers'     => Header::$codes,
            'debug'       => option('distantnative.retour.debug')
        ];
    }
}
