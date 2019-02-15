<?php

namespace distantnative\Retour;

require dirname(__DIR) . '/vendor/autoload.php';

load([
    'distantnative\\Retour\\Retour'    => 'models/Retour.php',
    'distantnative\\Retour\\Log'       => 'models/Log.php',
    'distantnative\\Retour\\Logs'      => 'models/Logs.php',
    'distantnative\\Retour\\Redirects' => 'models/Redirects.php',
    'distantnative\\Retour\\Stats'     => 'models/Stats.php',
    'distantnative\\Retour\\System'    => 'models/System.php'
], dirname(__DIR__));

$root        = __DIR__ . '/fixtures';
Retour::$dir = $root;
Log::$file   = $root . '/retour.data';
Logs::$file  = $root . '/404.log';
Stats::$file = $root . '/{x}.stats';

require __DIR__ . '/TestCase.php';
