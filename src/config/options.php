<?php

namespace distantnative\Retour;

$site = dirname(__DIR__, 4);

return [
    'api'         => true,
    'logs'        => true,
    'ignore'      => [],
    'config'      => $site . '/config/redirects.yml',
    'database'    => $site . '/logs/retour.sqlite',
    'deleteAfter' => false
];
