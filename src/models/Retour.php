<?php

namespace distantnative\Retour;

use Kirby\Http\Header;

class Retour
{
    public static function info(): array
    {
        return [
            'headers'     => Header::$codes,
            'logs'        => option('distantnative.retour.logs'),
            'deleteAfter' => option('distantnative.retour.deleteAfter')
        ];
    }

    public static function root(string $type = 'root'): ?string
    {
        $root  = dirname(__DIR__, 2);
        $src   = $root . '/src';
        $roots = [
            'assets'       => $src . '/assets',

            'redirects'    => option('distantnative.retour.config'),
            'logs'         => option('distantnative.retour.database'),
            'updated'      => option('distantnative.retour.updated'),

            'config'       => $config = $src . '/config',
            'migrations'   => $config . '/migrations',
            'translations' => $config . '/translations'
        ];

        return $roots[$type] ?? $root;
    }

}
