<?php

namespace distantnative\Retour;

load([
    'peterkahl\\locale\\locale'        => 'lib/locale.php',
    'distantnative\\Retour\\Retour'    => 'src/models/Retour.php',
    'distantnative\\Retour\\Log'       => 'src/models/Log.php',
    'distantnative\\Retour\\Redirects' => 'src/models/Redirects.php',
    'distantnative\\Retour\\Stats'     => 'src/models/Stats.php',
], __DIR__);

// config file location
Redirects::$file = option(
    'distantnative.retour.config',
    dirname(__DIR__, 2) . '/config/redirects.yml'
);

// database file location
Log::$file = option(
    'distantnative.retour.database',
    dirname(__DIR__, 2). '/logs/retour.sqlite'
);

\Kirby::plugin('distantnative/retour', [
    'options'      => ['api' => true],
    'api'          => require 'src/config/api.php',
    'hooks'        => require 'src/config/hooks.php',
    'translations' => require 'src/config/translations.php'
]);
