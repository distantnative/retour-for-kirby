<?php

@include_once __DIR__ . '/vendor/autoload.php';

function retour() {
    return distantnative\Retour\Retour::instance();
}

Kirby::plugin('distantnative/retour', [
    'api'          => require 'config/api.php',
    'hooks'        => require 'config/hooks.php',
    'routes'       => require 'config/routes.php',
    'translations' => require 'config/translations.php'
]);
