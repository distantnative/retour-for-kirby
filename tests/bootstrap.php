<?php

namespace distantnative\Retour;

require __DIR__ . '/../../../../kirby/bootstrap.php';
require __DIR__ . '/../load.php';

load([
    'distantnative\\Retour\\Retour'    => 'models/Retour.php',
    'distantnative\\Retour\\Log'       => 'models/Log.php',
    'distantnative\\Retour\\Logs'      => 'models/Logs.php',
    'distantnative\\Retour\\Redirects' => 'models/Redirects.php',
    'distantnative\\Retour\\Stats'     => 'models/Stats.php',
    'distantnative\\Retour\\System'    => 'models/System.php'
], dirname(__DIR__));