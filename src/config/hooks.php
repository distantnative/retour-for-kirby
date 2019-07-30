<?php

namespace distantnative\Retour;

use Kirby\Http\Router;

return [
    'route:after' => function ($route, $path, $method, $result) {
        if (empty($result) === true) {
            $log = new Log;

            try {
                $routes = Redirects::routes($log);
                $router = new Router($routes);
                return $router->call($path, $method);

            } catch (\Throwable $e) {
                $log->add([
                    'path' => $path
                ]);
                $log->close();
            }
        }
    }
];
