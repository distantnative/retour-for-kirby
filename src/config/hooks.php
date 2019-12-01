<?php

namespace distantnative\Retour;

use Kirby\Http\Router;

return [
    'route:after' => function ($route, $path, $method, $result) {
        if (empty($result) === true) {

            // skip ignored paths
            if (in_array($path, option('distantnative.retour.ignore')) === true) {
                return $result;
            }

            // If logging enable, initialize model
            if (option('distantnative.retour.logs') === true) {
                $log = new Log;
            }

            try {
                $routes = Redirects::routes($log ?? false);
                $router = new Router($routes);
                return $router->call($path, $method);

            } catch (\Throwable $e) {
                if (option('distantnative.retour.logs') === true) {
                    $log->add(['path' => $path]);
                    $log->close();
                }
            }
        }
    },
    'system.loadPlugins:after' => function () {
        Update::check();
    }
];
