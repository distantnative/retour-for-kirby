<?php

namespace distantnative\Retour;

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

// Load main models
load(['Retour', 'Redirects']);

// Set location for config file
Redirects::$file = option(
    'retour.config',
    dirname(__DIR__, 2) . '/config/redirects.yml'
);

// If logs are enabled…
if (option('retour.logging', true) === true) {
    // …load more models
    load(['Log', 'Stats']);

    // …set location for database file
    Log::$file = option(
        'retour.database',
        dirname(__DIR__, 2). '/logs/retour.sqlite'
    );
}

// Register all plugin components
\Kirby::plugin('distantnative/retour', [
    'options'      => ['api' => true],
    'api'          => require 'src/config/api.php',
    'hooks'        => require 'src/config/hooks.php' ?? [],
    'translations' => require 'src/config/translations.php'
]);
