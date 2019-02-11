<?php

namespace distantnative\Retour;

load([
    'distantnative\\retour\\retour'    => 'models/Retour.php',
    'distantnative\\retour\\log'       => 'models/Log.php',
    'distantnative\\retour\\logs'      => 'models/Logs.php',
    'distantnative\\retour\\redirects' => 'models/Redirects.php',
    'distantnative\\retour\\stats'     => 'models/Stats.php',
    'distantnative\\retour\\system'    => 'models/System.php'
], __DIR__);

$retour         = new Retour;
$root           = kirby()->root('site');
Retour::$dir    = $root . '/logs/retour';
Logs::$file     = $root . '/logs/retour/404.log';
Stats::$file    = $root . '/logs/retour/{x}.stats';

\Kirby::plugin('distantnative/retour', [
    'api'          => require 'config/api.php',
    'hooks'        => require 'config/hooks.php',
    'options'      => require 'config/options.php',
    'routes'       => $retour->redirects()->routes(),
    'translations' => [
        'en' => require 'config/translations/en.php',
        'de' => require 'config/translations/de.php'
    ]
]);
