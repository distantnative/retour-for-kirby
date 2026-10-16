<?php

namespace Kirby\Retour;

use Kirby\Exception\InvalidArgumentException;

/**
 * Paths that should not be logged as failures,
 * from the `ignore` option as well as the ones
 * managed in the Panel and stored in the config file
 */
class Ignores
{
	/**
	 * Paths that bots commonly probe for vulnerabilities,
	 * mostly of WordPress installations
	 */
	public const COMMON = [
		'wp-(:all)',
		'(:all)/wp-(:all)',
		'xmlrpc.php',
		'(:all)/xmlrpc.php',
		'.env',
		'(:all)/.env',
		'.git/(:all)'
	];

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

	public function all(): array
	{
		return [...$this->config(), ...$this->custom()];
	}

	/**
	 * Returns the patterns from the `ignore` option
	 */
	public function config(): array
	{
		return $this->retour->option('ignore');
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

		foreach ($patterns as $pattern) {
			// skip invalid entries (e.g. from the `ignore` option
			// or empty list items in the config file)
			if (is_string($pattern) === false || $pattern === '') {
				continue;
			}

			if (Pattern::matches($pattern, $path, 'iu') === true) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Replaces the patterns managed in the Panel
	 *
	 * @throws InvalidArgumentException If a pattern is invalid
	 */
	public function set(array $patterns): array
	{
		$patterns = array_map(trim(...), $patterns);
		$patterns = array_filter($patterns, fn ($pattern) => $pattern !== '');
		$patterns = array_values(array_unique($patterns));

		foreach ($patterns as $pattern) {
			if (Pattern::isValid($pattern) === false) {
				throw new InvalidArgumentException(
					message: 'Invalid pattern: ' . $pattern
				);
			}
		}

		$this->retour->config()->write('ignore', $patterns);

		return $patterns;
	}
}
