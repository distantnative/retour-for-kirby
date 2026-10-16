<?php

namespace Kirby\Retour\Panel;

use Kirby\Exception\NotFoundException;
use Kirby\Retour\Redirect;
use Kirby\Retour\Retour;

/**
 * Turns the ids used in dialog and drawer URLs
 * back into the paths or redirects they stand for
 * (see `Redirect::id()` and the `id()` method
 * of the collection view for the reasons)
 */
class Id
{
	public static function toPath(string $id): string
	{
		return str_replace("\x1D", '/', urldecode($id));
	}

	/**
	 * @throws NotFoundException If the redirect doesn't exist
	 */
	public static function toRedirect(string $id): Redirect
	{
		return Retour::instance()->redirects()->get(urldecode($id)) ?? throw new NotFoundException(
			message: 'Redirect not found'
		);
	}
}
