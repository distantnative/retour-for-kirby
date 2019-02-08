<?php

load([
    // lib
    'distantnative\\Retour\\Store' => 'models/Store.php',

    // models
    'distantnative\\Retour'            => 'models/Retour.php',
    'distantnative\\Retour\\Log'       => 'models/Log.php',
    'distantnative\\Retour\\Redirects' => 'models/Redirects.php',
    'distantnative\\Retour\\Stats'     => 'models/Stats.php',
    'distantnative\\Retour\\System'    => 'models/System.php'
], __DIR__);

$retour = new distantnative\Retour;

Kirby::plugin('distantnative/retour', [
    'api'          => require 'config/api.php',
    'hooks'        => require 'config/hooks.php',
    'options'      => require 'config/options.php',
    'routes'       => $retour->redirects()->routes(),
    'translations' => [
        'en' => require 'config/translations/en.php',
        'de' => require 'config/translations/de.php'
    ]
]);
