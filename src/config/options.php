<?php

namespace distantnative\Retour;

$plugin = dirname(__DIR__, 2);
$site   = dirname(__DIR__, 4);

return [
    'api'         => true,
    'logs'        => true,
    'ignore'      => [],
    'config'      => $site . '/config/redirects.yml',
    'database'    => $site . '/logs/retour.sqlite',
    'updated'     => $plugin . '/.updated',
    'deleteAfter' => false
];
