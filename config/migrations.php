<?php

namespace distantnative\Retour;

return [
    '3.0.0' => function (Retour $retour) {
        // transform route definitions
        $routes = array_map(function ($route) {

            $route['active']   = true;
            $route['priority'] = false;

            if (
                ($route['status'] ?? false) === false ||
                $route['status'] === 'disabled' ||
                empty($route['status']) === true
            ) {
                $route['active'] = false;
                $route['status'] = 301;
            }

            return $route;
        }, $retour->config);

        // create new config file structure
        $data = [
            'routes' => [
                'manual'  => $routes,
                'tracked' => []
            ]
        ];

        // write to file
        $retour->update($data);
    }
];
