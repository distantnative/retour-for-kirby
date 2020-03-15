<?php

namespace distantnative\Retour;

use Kirby\Cms\App;

@include_once __DIR__ . '/vendor/autoload.php';

App::plugin('distantnative/retour', [
    'options'      => require 'config/options.php',
    'api'          => require 'config/api.php',
    'hooks'        => require 'config/hooks.php',
    'translations' => require 'config/translations.php'
]);
