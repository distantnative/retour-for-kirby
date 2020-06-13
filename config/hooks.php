<?php

namespace distantnative\Retour;

use Kirby\Http\Router;

return [
    'route:after' => function ($route, $path, $method, $result, $final) {
        if ($final === true && empty($result) === true) {

            // skip ignored paths
            $ignore = option('distantnative.retour.ignore');
            if (in_array($path, $ignore) === true) {
                return $result;
            }

            $retour = Retour::instance();

            try {
                $routes = $retour->routes()->toRules(false);
                $router = new Router($routes);
                return $router->call($path, $method);

            } catch (\Throwable $e) {
                // If logging enabled, add record
                if (option('distantnative.retour.logs') === true) {
                    $retour->log()->create(['path' => $path]);
                }
            }
        }
    }
];
