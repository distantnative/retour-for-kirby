<?php

namespace distantnative\Retour;

return [
    '3.0.0' => function (Retour $retour) {
        // transform route definitions
        $routes = array_map(function ($route) {
            $route['priority'] = false;
            return $route;
        }, $retour->config ?? []);

        // create new config file structure
        $data = ['routes' =>  $routes];

        // write to file
        $retour->update($data);
    }
];
