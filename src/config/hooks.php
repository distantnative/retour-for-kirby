<?php

namespace distantnative\Retour;

use Kirby\Http\Router;

return [
    'route:after' => function ($route, $path, $method, $result) {
        if (empty($result) === true) {

            // skip ignored paths
            $ignore = option('distantnative.retour.ignore');
            if (in_array($path, $ignore) === true) {
                return $result;
            }

            $retour = Retour::instance();

            try {
                $routes = $retour->redirects()->toRoutes();
                $router = new Router($routes);
                return $router->call($path, $method);

            } catch (\Throwable $e) {
                // If logging enable, add record
                if (option('distantnative.retour.logs') === true) {
                    $retour->logs()->create(['path' => $path]);
                }
            }
        }
    }
];
