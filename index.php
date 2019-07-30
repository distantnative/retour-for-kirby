<?php

namespace distantnative\Retour;

require 'lib/locale.php';

load([
    'distantnative\\Retour\\Retour'    => 'src/models/Retour.php',
    'distantnative\\Retour\\Log'       => 'src/models/Log.php',
    'distantnative\\Retour\\Redirects' => 'src/models/Redirects.php',
    'distantnative\\Retour\\Stats'     => 'src/models/Stats.php',
], __DIR__);

Redirects::$file = dirname(__DIR__, 2) . '/config/redirects.yml';
Log::$file       = __DIR__ . '/logs/retour.sqlite';

\Kirby::plugin('distantnative/retour', [
    'routes' => function ($kirby) {
        return Redirects::routes($kirby);
    },
    'options'      => require 'src/config/options.php',
    'api'          => require 'src/config/api.php',
    'hooks'        => require 'src/config/hooks.php',
    'translations' => require 'src/config/translations.php'
]);
