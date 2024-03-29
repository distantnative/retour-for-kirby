<?php

namespace Kirby\Retour;

use Kirby\Http\Route;
use Kirby\Http\Router;
use Throwable;

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
			$retour = Retour::instance();

			// Skip ignored paths
			if ($retour->ignore($path) === true) {
				return $result;
			}

			try {
				// find non-priority redirect route for current path
				// and call if possible
				$routes = $retour->redirects()->toRoutes(false);
				$router = new Router($routes);
				return $router->call($path, $method);
			} catch (Throwable) {
				// log 404 if feature enabled
				if ($retour->hasLog() === true) {
					$retour->log()->add(['path' => $path]);
				}
			}
		}
	}
];
