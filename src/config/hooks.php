<?php

namespace distantnative\Retour;

use Kirby\Http\Router;

return [
    'route:after' => function ($route, $path, $method, $result, $final) {
        if ($final === true && empty($result) === true) {

            // skip ignored paths
            if (in_array($path, option('distantnative.retour.ignore')) === true) {
                return $result;
            }

            try {
                $routes = Redirects::routes();
                $router = new Router($routes);
                return $router->call($path, $method);
            } catch (\Throwable $e) {
                // If logging enable, initialize model and add record
                if (option('distantnative.retour.logs') === true) {
                    (new Log)->add(['path' => $path]);
                }
            }
        }
    }
];
