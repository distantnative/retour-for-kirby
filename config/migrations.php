<?php

namespace distantnative\Retour;

return [
    '3.0.0' => function () {
        $config = $this->config();

        // transform route definitions
        $routes = array_map(function ($route) {
            $route['priority'] = false;
            return $route;
        }, $config->data());

        // write to file
        $config->overwrite(['routes' =>  $routes]);
    }
];
