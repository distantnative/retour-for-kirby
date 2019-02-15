<?php

namespace distantnative\Retour;

load([
    'distantnative\\Retour\\Retour'    => 'models/Retour.php',
    'distantnative\\Retour\\Log'       => 'models/Log.php',
    'distantnative\\Retour\\Logs'      => 'models/Logs.php',
    'distantnative\\Retour\\Redirects' => 'models/Redirects.php',
    'distantnative\\Retour\\Stats'     => 'models/Stats.php',
    'distantnative\\Retour\\System'    => 'models/System.php'
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
    'translations' => require 'config/translations.php'
]);
