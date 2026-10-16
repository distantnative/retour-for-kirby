<?php

namespace Kirby\Retour;

use Kirby\Http\Route;

/**
 * Route patterns with placeholders like `(:any)`
 * as used for redirects and ignored paths
 */
class Pattern
{
	/**
	 * Regular expressions by flags and pattern, as the same
	 * patterns get matched against many paths (e.g. when
	 * removing the failures of newly ignored paths)
	 */
	protected static array $regexes = [];

	/**
	 * Checks if the pattern compiles to a valid regular expression
	 */
	public static function isValid(string $pattern): bool
	{
		return @preg_match(static::regex($pattern), '') !== false;
	}

	/**
	 * Checks if the path matches the pattern; invalid patterns
	 * never match and don't emit warnings, as they must not
	 * break the handling of every 404
	 */
	public static function matches(
		string $pattern,
		string $path,
		string $flags = 'u'
	): bool {
		$regex = static::$regexes[$flags][$pattern] ??= static::regex($pattern, $flags);
		return @preg_match($regex, $path) === 1;
	}

	/**
	 * Returns the anchored regular expression for the
	 * pattern the same way Kirby's router builds it,
	 * but also anchoring alternatives like `foo|bar`
	 */
	public static function regex(string $pattern, string $flags = 'u'): string
	{
		$route = new Route('', 'GET', fn () => null);
		return '#^(?:' . $route->regex(trim($pattern, '/')) . ')$#' . $flags;
	}
}
