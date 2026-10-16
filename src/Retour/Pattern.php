<?php

namespace Kirby\Retour;

use Kirby\Http\Route;

/**
 * Route patterns with placeholders like `(:any)`
 * as used for redirects and ignored paths
 */
class Pattern
{
	protected static array $regexes = [];

	public static function hasPlaceholder(string $pattern): bool
	{
		return str_contains($pattern, '(') === false;
	}

	/**
	 * Checks if the pattern compiles to a valid regular expression
	 */
	public static function isValid(string $pattern): bool
	{
		return @preg_match(static::regex($pattern), '') !== false;
	}

	/**
	 * Checks if the path matches the pattern
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
	 * Returns the anchored regular expression for the pattern
	 */
	public static function regex(
		string $pattern,
		string $flags = 'u'
	): string {
		$route   = new Route('', 'GET', fn () => null);
		$pattern = trim($pattern, '/');
		return '#^(?:' . $route->regex($pattern) . ')$#' . $flags;
	}
}
