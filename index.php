<?php

require 'load.php';

$retour = new distantnative\Retour;

Kirby::plugin('distantnative/retour', [
    'api'          => require 'config/api.php',
    'hooks'        => require 'config/hooks.php',
    'options'      => require 'config/options.php',
    'routes'       => $retour->redirects()->routes(),
    'translations' => [
        'en' => require 'config/translations/en.php',
        'de' => require 'config/translations/de.php'
    ]
]);
