<?php

namespace distantnative\Retour;

use Kirby\Http\Header;

class Retour
{

    /**
     * Return information for Panel
     *
     * @return array
     */
    public static function info(): array
    {
        return [
            'headers'     => Header::$codes,
            'logs'        => option('distantnative.retour.logs'),
            'deleteAfter' => option('distantnative.retour.deleteAfter')
        ];
    }

    /**
     * Helper to get root paths to plugin locations
     *
     * @param string $type
     *
     * @return string|null
     */
    public static function root(string $type = 'root'): ?string
    {
        $root  = dirname(__DIR__, 2);
        $src   = $root . '/src';

        $roots = [
            'assets'       => $root . '/assets',

            'redirects'    => option('distantnative.retour.config'),
            'logs'         => option('distantnative.retour.database'),

            'config'       => $config = $src . '/config',
            'migrations'   => $config . '/migrations',
            'translations' => $config . '/translations'
        ];

        return $roots[$type] ?? $root;
    }
}
