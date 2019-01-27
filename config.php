<?php

$retour = new distantnative\Retour;

Kirby::plugin('distantnative/retour', [
    'api'          => require 'src/config/api.php',
    'hooks'        => require 'src/config/hooks.php',
    'options'      => require 'src/config/options.php',
    'routes'       => $retour->redirects()->routes(),
    'translations' => require 'src/config/translations.php',
]);
