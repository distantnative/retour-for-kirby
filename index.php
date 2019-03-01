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

Retour::$dir     = __DIR__ . '/../../logs/retour';
Logs::$file      = __DIR__ . '/../../logs/retour/404.log';
Stats::$file     = __DIR__ . '/../../logs/retour/{x}.stats';
Redirects::$file = __DIR__ . '/../../config/retour.yml';

\Kirby::plugin('distantnative/retour', [
    'api'          => require 'config/api.php',
    'hooks'        => require 'config/hooks.php',
    'options'      => ['api' => true],
    'routes'       => function ($kirby) {
        return Redirects::routes($kirby);
    },
    'translations' => require 'config/translations.php'
]);

// include 'samples.php';
