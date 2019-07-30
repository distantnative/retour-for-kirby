<?php

namespace distantnative\Retour;

return [
    'route:after' => function ($route, $path, $method, $result) {
        if (empty($result) === true) {
            $log = new Log;
            $log->add([
                'path' => $path
            ]);
            $log->close();
        }
    }
];
