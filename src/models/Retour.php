<?php

namespace distantnative\Retour;

use Kirby\Http\Header;

class Retour
{
    public static function info(): array
    {
        return [
            'headers'     => Header::$codes,
            'logs'        => option('retour.logs', true),
            'deleteAfter' => option('retour.deleteAfter', false)
        ];
    }

    public static function root(string $type = 'root'): ?string
    {
        $root = dirname(__DIR__, 2);
        $src  = $root . '/src';
        $site = dirname(__DIR__, 4);

        $roots = [
            'assets'       => $src . '/assets',

            'redirects'    => option('retour.config', $site . '/config/redirects.yml'),
            'logs'         => option('retour.database', $site . '/logs/retour.sqlite'),

            'config'       => $config = $src . '/config',
            'migrations'   => $config . '/migrations',
            'translations' => $config . '/translations'
        ];

        return $roots[$type] ?? $root;
    }

}
