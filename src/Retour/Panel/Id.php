<?php

namespace Kirby\Retour\Panel;

/**
 * Turns the ids used in dialog and drawer URLs
 * back into the paths they stand for
 * (see `Redirect::id()` and the `id()` method
 * of the collection view for the reasons)
 */
class Id
{
	public static function toPath(string $id): string
	{
		return str_replace("\x1D", '/', urldecode($id));
	}
}
