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
			$retour = Retour::instance();
			$router = new Router($retour->redirects()->toRoutes($path, priority: true));

			try {
				$route = $router->find($path, $this->method());
			} catch (Exception) {
				$this->next();
			}

			$kirby = $retour->kirby();

			// Kirby's language routes, which set the language of the path, run after the plugin routes
			if ($kirby->multilang() === true) {
				$language   = $kirby->languages()->filter('baseurl', $kirby->url())->findByPath($path);
				$language ??= $kirby->defaultLanguage();

				$kirby->setCurrentTranslation($language->code());
				$kirby->setCurrentLanguage($language->code());
			}

			return $route->action()->call($route, ...$route->arguments());
		}
	]
];
