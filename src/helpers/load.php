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

    \load($map, dirname(__DIR__, 2));
}
