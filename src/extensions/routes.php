<?php

namespace Kirby\Retour;

use Exception;
use Kirby\Http\Router;

return [
	[
		// one route for all redirects with priority over pages,
		// so the redirects are only loaded for requests that reach it
		// (not for the Panel, the API or media files)
		'pattern' => '(:all)',
		'method'  => 'GET|HEAD',
		'action'  => function (string $path) {
			$router = new Router(Retour::instance()->redirects()->toRoutes(true));

			try {
				$route = $router->find($path, $this->method());
			} catch (Exception) {
				$this->next();
			}

			return $route->action()->call($route, ...$route->arguments());
		}
	]
];
