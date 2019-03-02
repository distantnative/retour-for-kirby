<?php

namespace distantnative\Retour;

require dirname(__DIR__) . '/vendor/autoload.php';

load([
    'distantnative\\Retour\\Log'       => 'models/Log.php',
    'distantnative\\Retour\\Logs'      => 'models/Logs.php',
    'distantnative\\Retour\\Redirects' => 'models/Redirects.php',
    'distantnative\\Retour\\Stats'     => 'models/Stats.php',
    'distantnative\\Retour\\System'    => 'models/System.php'
], dirname(__DIR__));

Logs::$dir       = __DIR__ . '/fixtures';
Log::$file       = Logs::$dir . '/retour.data';
Logs::$file      = Logs::$dir . '/404.log';
Stats::$file     = Logs::$dir . '/{x}.stats';
Redirects::$file = Logs::$dir . '/redirects.yml';

require __DIR__ . '/TestCase.php';
