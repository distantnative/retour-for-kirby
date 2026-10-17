<?php

namespace Kirby\Retour;

use Exception;
use Kirby\Http\Router;

return [
	[
		// loads the priority redirects only for requests that reach it (not the Panel, API or media)
		'pattern' => '(:all)',
		'method'  => 'GET|HEAD',
		'action'  => function (string $path) {
			$router = new Router(Retour::instance()->redirects()->toRoutes($path, true));

			try {
				$route = $router->find($path, $this->method());
			} catch (Exception) {
				$this->next();
			}

			return $route->action()->call($route, ...$route->arguments());
		}
	]
];
