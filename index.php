<?php

load([
    'distantnative\\retour\\retour'    => 'models/Retour.php',
    'distantnative\\retour\\log'       => 'models/Log.php',
    'distantnative\\retour\\logs'      => 'models/Logs.php',
    'distantnative\\retour\\redirects' => 'models/Redirects.php',
    'distantnative\\retour\\stats'     => 'models/Stats.php',
    'distantnative\\retour\\system'    => 'models/System.php'
], __DIR__);

$retour = new distantnative\Retour\Retour;

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
