<?php

namespace distantnative\Retour;

require dirname(__DIR__) . '/vendor/autoload.php';

load([
    'distantnative\\Retour\\Retour'    => 'models/Retour.php',
    'distantnative\\Retour\\Log'       => 'models/Log.php',
    'distantnative\\Retour\\Logs'      => 'models/Logs.php',
    'distantnative\\Retour\\Redirects' => 'models/Redirects.php',
    'distantnative\\Retour\\Stats'     => 'models/Stats.php',
    'distantnative\\Retour\\System'    => 'models/System.php'
], dirname(__DIR__));

Retour::$dir     = __DIR__ . '/fixtures';
Log::$file       = Retour::$dir . '/retour.data';
Logs::$file      = Retour::$dir . '/404.log';
Stats::$file     = Retour::$dir . '/{x}.stats';
Redirects::$file = Retour::$dir . '/redirects.yml';

require __DIR__ . '/TestCase.php';
