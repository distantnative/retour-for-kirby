<?php

namespace Kirby\Retour;

use Kirby\Http\Router;
use Throwable;

return [
	'route:after' => function (
		string $path,
		string $method,
		mixed $result,
		bool $final
	) {
		// only for requests that Kirby would answer with a 404
		if (
			$final === false ||
			in_array($result, [null, false, '', []], true) === false
		) {
			return null;
		}

		$retour = Retour::instance();

		try {
			$result = Router::execute(
				$path,
				$method,
				$retour->redirects()->toRoutes($path, priority: false)
			);
		} catch (Throwable) {
			$result = null;
		}

		if (
			$result === null &&
			$method !== 'HEAD' &&
			$retour->hasLog() === true &&
			$retour->ignores()->matchesVisitor() === false &&
			$retour->ignores()->matches($path) === false
		) {
			$retour->log()->add(['path' => $path]);
		}

		return $result;
	}
];
