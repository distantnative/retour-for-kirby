<?php

return [
    'route:after' => function ($route, $path, $method, $result) {
        if (empty($result) === true) {
            distantnative\Retour\Retour::store($path, true);
        }
    }
];
