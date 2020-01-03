<?php

namespace distantnative\Retour;

use Kirby\Cms\App;

@include_once __DIR__ . '/vendor/autoload.php';

App::plugin('distantnative/retour', [
    'options'      => require 'src/config/options.php',
    'api'          => require 'src/config/api.php',
    'hooks'        => require 'src/config/hooks.php',
    'translations' => require 'src/config/translations.php'
]);
