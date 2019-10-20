<?php

namespace distantnative\Retour;

load(['Retour', 'Redirects', 'Update']);

if (option('retour.logs', true) === true) {
    load(['Log', 'Stats']);
}

Update::check();

\Kirby::plugin('distantnative/retour', [
    'options'      => ['api' => true],
    'api'          => require 'src/config/api.php',
    'hooks'        => require 'src/config/hooks.php' ?? [],
    'translations' => require 'src/config/translations.php'
]);


function load(array $classes): void
{
    $map = [];

    foreach ($classes as $class) {
        $namespace       = 'distantnative\\Retour\\' . $class;
        $path            = 'src/models/' . $class . '.php';
        $map[$namespace] = $path;
    }

    \load($map, __DIR__);
}
