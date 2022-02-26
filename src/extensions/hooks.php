<?php

namespace distantnative\Retour;

use Kirby\Http\Route;
use Kirby\Http\Router;

/**
 * Sets up route hook to intercept
 * 404s for redirecting and logging
 *
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */

return [
    'route:after' => function (
        Route $route,
        string $path,
        string $method,
        $result,
        bool $final
    ) {
        if (
            $final === true &&
            empty($result) === true
        ) {
            $retour = Plugin::instance();

            // skip ignored paths
            if (in_array($path, $retour->option('ignore', [])) === true) {
                return $result;
            }

            try {
                // find non-priority redirect route for current path
                // and call if possible
                $routes = $retour->redirects()->toRoutes(false);
                $router = new Router($routes);
                return $router->call($path, $method);
            } catch (\Throwable $e) {
                // log 404 if feature enabled
                if ($retour->hasLog() === true) {
                    $retour->log()->add(['path' => $path]);
                }
            }
        }
    }
];
