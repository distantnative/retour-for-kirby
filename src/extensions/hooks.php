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

			try {
				// find non-priority redirect route for current path
				// and call if possible
				$routes = $retour->redirects()->toRoutes(false);
				$router = new Router($routes);
				$result = $router->call($path, $method);
			} catch (Throwable) {
				$result = null;
			}

			if ($result !== null) {
				return $result;
			}

			// log 404 if feature enabled and path not ignored
			$log = $retour->log();

			if (
				$log !== null &&
				$retour->ignore($path) === false
			) {
				$log->add(['path' => $path]);
			}
		}
	}
];
