<?php

namespace distantnative\Retour;

use Kirby\Cms\App;

load([
    'Retour',
    'Redirects',
    'Log',
    'Stats',
    'Update'
]);

App::plugin('distantnative/retour', [
    'options'      => require 'src/config/options.php',
    'api'          => require 'src/config/api.php',
    'hooks'        => require 'src/config/hooks.php' ?? [],
    'translations' => require 'src/config/translations.php'
]);

function load(array $classes): void
{
    $map = [];

    foreach ($classes as $class) {
        $namespace       = 'distantnative\\Retour\\' . $class;
        $map[$namespace] = 'src/models/' . $class . '.php';
    }

    \load($map, __DIR__);
}
