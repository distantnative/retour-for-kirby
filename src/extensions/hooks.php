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
			// redirects without priority only
			// apply if no page or route matched
			$result = Router::execute(
				$path,
				$method,
				$retour->redirects()->toRoutes(false)
			);
		} catch (Throwable) {
			$result = null;
		}

		if ($result !== null) {
			return $result;
		}

		$log = $retour->log();

		if (
			$log !== null &&
			$retour->ignore($path) === false
		) {
			$log->add(['path' => $path]);
		}

		return null;
	}
];
