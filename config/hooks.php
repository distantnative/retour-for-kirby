<?php

namespace distantnative\Retour;

return [
    'route:after' => function ($route, $path, $method, $result) {
        if (empty($result) === true) {
            Logs::store($path, 'failed');
        }
    }
];
