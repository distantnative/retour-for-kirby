<?php

namespace Kirby\Retour;

use Kirby\Http\Route;

/**
 * Paths that should not be logged as failures,
 * from the `ignore` option as well as the ones
 * managed in the Panel and stored in the config file
 */
class Ignores
{
	public function __construct(
		protected Retour $retour
	) {
	}

	/**
	 * Adds a pattern to the ones managed in the Panel
	 */
	public function add(string $pattern): array
	{
		return $this->set([...$this->custom(), $pattern]);
	}

	/**
	 * Returns all patterns
	 */
	public function all(): array
	{
		return [...$this->config(), ...$this->custom()];
	}

	/**
	 * Returns the patterns from the `ignore` option
	 */
	public function config(): array
	{
		return $this->retour->option('ignore', []);
	}

	/**
	 * Returns the patterns managed in the Panel
	 */
	public function custom(): array
	{
		return $this->retour->config()->data('ignore') ?? [];
	}

	/**
	 * Checks if the path matches any of the patterns
	 * (by default all patterns)
	 */
	public function matches(string $path, array|null $patterns = null): bool
	{
		$patterns ??= $this->all();

		if ($patterns === []) {
			return false;
		}

		// temporary route for regex matching
		$route   = new Route($path, 'GET', fn () => null);
		$pattern = $route->regex(implode('|', $patterns));
		return preg_match('!^(' . $pattern . ')$!i', $path) === 1;
	}

	/**
	 * Replaces the patterns managed in the Panel
	 */
	public function set(array $patterns): array
	{
		$patterns = array_map(trim(...), $patterns);
		$patterns = array_filter($patterns, fn ($pattern) => $pattern !== '');
		$patterns = array_values(array_unique($patterns));

		$this->retour->config()->write('ignore', $patterns);

		return $patterns;
	}
}
