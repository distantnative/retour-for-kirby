<?php

namespace Kirby\Retour\Panel;

use Kirby\Exception\NotFoundException;
use Kirby\Retour\Redirect;
use Kirby\Retour\Retour;

/**
 * Turns paths into the ids of dialog and drawer URLs and back;
 * base64url only uses characters that `(:any)` accepts and that
 * web servers pass on unchanged, unlike encoded slashes
 * (https://github.com/distantnative/retour-for-kirby/issues/300)
 */
class Id
{
	/**
	 * @throws NotFoundException If the id isn't valid base64url
	 */
	public static function decode(string $id): string
	{
		$path = base64_decode(strtr($id, '-_', '+/'), true);

		if ($path === false) {
			throw new NotFoundException(message: 'Invalid id');
		}

		return $path;
	}

	public static function encode(string $path): string
	{
		return rtrim(strtr(base64_encode($path), '+/', '-_'), '=');
	}

	/**
	 * @throws NotFoundException If the redirect doesn't exist
	 */
	public static function redirect(string $id): Redirect
	{
		return Retour::instance()->redirects()->get(static::decode($id)) ?? throw new NotFoundException(
			message: 'Redirect not found'
		);
	}
}
