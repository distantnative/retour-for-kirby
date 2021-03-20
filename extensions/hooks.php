<?php

namespace distantnative\Retour;

use Kirby\Http\Route;
use Kirby\Http\Router;

return [
    'route:after' => function (
        Route $route,
        string $path,
        string $method,
        string $result,
        bool $final
    ) {
        if ($final === true && empty($result) === true) {

            // skip ignored paths
            $ignore = option('distantnative.retour.ignore', []);
            if (in_array($path, $ignore) === true) {
                return $result;
            }

            $retour = retour();

            try {
                $routes = $retour->redirects()->toRoutes(false);
                $router = new Router($routes);
                return $router->call($path, $method);
            } catch (\Throwable $e) {
                if (Retour::meta()['hasLog'] !== false) {
                    $retour->log()->add(['path' => $path]);
                }
            }
        }
    }
];
