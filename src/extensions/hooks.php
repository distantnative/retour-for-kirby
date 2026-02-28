<?php

namespace Kirby\Retour;

use Kirby\Http\Route;
use Kirby\Http\Router;
use Throwable;

return [
	'route:after' => function (
		Route $route,
		string $path,
		string $method,
		mixed $result,
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
