<?php

@include_once __DIR__ . '/vendor/autoload.php';

load([
    'distantnative\\Retour\\Retour'    => __DIR__ . '/models/Retour.php',
    'distantnative\\Retour\\Config'    => __DIR__ . '/models/Config.php',
    'distantnative\\Retour\\Log'       => __DIR__ . '/models/Log.php',
    'distantnative\\Retour\\Redirect'  => __DIR__ . '/models/Redirect.php',
    'distantnative\\Retour\\Redirects' => __DIR__ . '/models/Redirects.php'
]);

function retour()
{
    return distantnative\Retour\Retour::instance();
}

Kirby::plugin('distantnative/retour', [
    'api'          => require 'extensions/api.php',
    'hooks'        => require 'extensions/hooks.php',
    'routes'       => require 'extensions/routes.php',
    'translations' => require 'extensions/translations.php'
]);
