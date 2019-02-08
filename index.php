<?php

load([
    // lib
    'distantnative\\retour\\store' => 'src/models/Store.php',

    // models
    'distantnative\\retour'            => 'src/models/Retour.php',
    'distantnative\\retour\\log'       => 'src/models/Log.php',
    'distantnative\\retour\\redirects' => 'src/models/Redirects.php',
    'distantnative\\retour\\stats'     => 'src/models/Stats.php',
    'distantnative\\retour\\system'    => 'src/models/System.php'
], __DIR__);

$retour = new distantnative\Retour;

Kirby::plugin('distantnative/retour', [
    'api'          => require 'config/api.php',
    'hooks'        => require 'config/hooks.php',
    'options'      => require 'config/options.php',
    'routes'       => $retour->redirects()->routes(),
    'translations' => require 'config/translations.php',
]);
