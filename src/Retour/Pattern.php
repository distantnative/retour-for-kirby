<?php

namespace Kirby\Retour;

use Kirby\Http\Route;

/**
 * Route patterns like `(:any)` of redirects and ignored paths
 */
class Pattern
{
	protected static array $regexes = [];

	public static function hasPlaceholder(string $pattern): bool
	{
		return str_contains($pattern, '(') === true;
	}

	public static function isValid(string $pattern): bool
	{
		return @preg_match(static::regex($pattern), '') !== false;
	}

	public static function matches(
		string $pattern,
		string $path,
		string $flags = 'u'
	): bool {
		$regex = static::$regexes[$flags][$pattern] ??= static::regex($pattern, $flags);
		return @preg_match($regex, $path) === 1;
	}

	public static function regex(
		string $pattern,
		string $flags = 'u'
	): string {
		$route   = new Route('', 'GET', fn () => null);
		$pattern = trim($pattern, '/');
		return '#^(?:' . $route->regex($pattern) . ')$#' . $flags;
	}
}
