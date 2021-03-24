<?php

load([
    'distantnative\\Retour\\Retour'    => __DIR__ . '/src/models/Retour.php',
    'distantnative\\Retour\\Config'    => __DIR__ . '/src/models/Config.php',
    'distantnative\\Retour\\Log'       => __DIR__ . '/src/models/Log.php',
    'distantnative\\Retour\\Redirect'  => __DIR__ . '/src/models/Redirect.php',
    'distantnative\\Retour\\Redirects' => __DIR__ . '/src/models/Redirects.php'
]);

function retour()
{
    return distantnative\Retour\Retour::instance();
}

Kirby::plugin('distantnative/retour', [
    'api'          => require 'src/extensions/api.php',
    'hooks'        => require 'src/extensions/hooks.php',
    'routes'       => require 'src/extensions/routes.php',
    'translations' => require 'src/extensions/translations.php'
]);
