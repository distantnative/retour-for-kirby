<?php

namespace Kirby\Retour;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;

/**
 * Paths that should not be logged as failures,
 * from the `ignore` option as well as the ones
 * managed in the Panel and stored in the config file,
 * and IP addresses whose requests should not be logged
 * at all, from the `ignoreIps` option
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

	/**
	 * Returns the IP address in binary form (IPv4 addresses
	 * mapped to IPv6 like `::ffff:192.0.2.1` in their IPv4 form)
	 * or `null` if it's not a valid address
	 */
	protected static function binary(string $ip): string|null
	{
		$binary = inet_pton(trim($ip));

		if ($binary === false) {
			return null;
		}

		if (
			strlen($binary) === 16 &&
			str_starts_with($binary, str_repeat("\x00", 10) . "\xFF\xFF") === true
		) {
			return substr($binary, 12);
		}

		return $binary;
	}

	/**
	 * Returns the patterns managed in the Panel
	 */
	public function custom(): array
	{
		return $this->retour->config()->get('ignore') ?? [];
	}

	/**
	 * Checks if the IP address (in binary form) is the address
	 * or inside the CIDR range of the entry, e.g. `192.0.2.0/24`
	 */
	protected static function inRange(string $ip, string $entry): bool
	{
		[$address, $bits] = explode('/', $entry, 2) + [1 => null];
		$address = static::binary($address);

		// invalid entry or of the other IP version
		if ($address === null || strlen($address) !== strlen($ip)) {
			return false;
		}

		$max  = strlen($ip) * 8;
		$bits = $bits === null ? $max : filter_var($bits, FILTER_VALIDATE_INT, [
			'options' => ['min_range' => 0, 'max_range' => $max]
		]);

		if ($bits === false) {
			return false;
		}

		// compare the full bytes of the prefix, then the remaining bits
		$bytes = intdiv($bits, 8);

		if (strncmp($ip, $address, $bytes) !== 0) {
			return false;
		}

		if ($bits % 8 === 0) {
			return true;
		}

		$mask = chr((0xFF << (8 - $bits % 8)) & 0xFF);

		return ($ip[$bytes] & $mask) === ($address[$bytes] & $mask);
	}

	/**
	 * Returns the IP addresses and ranges from the `ignoreIps` option
	 */
	public function ips(): array
	{
		return A::wrap($this->retour->option('ignoreIps'));
	}

	/**
	 * Checks if the path matches any of the patterns
	 * (by default all patterns)
	 */
	public function matches(string $path, array|null $patterns = null): bool
	{
		$patterns ??= [...$this->option(), ...$this->custom()];

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
	 * Checks if the IP address is one of the addresses
	 * or inside one of the ranges of the `ignoreIps` option
	 */
	public function matchesIp(string|null $ip): bool
	{
		$entries = $this->ips();

		if ($entries === [] || $ip === null) {
			return false;
		}

		$ip = static::binary($ip);

		if ($ip === null) {
			return false;
		}

		foreach ($entries as $entry) {
			// skip invalid entries
			if (is_string($entry) === false) {
				continue;
			}

			if (static::inRange($ip, trim($entry)) === true) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if requests from the current visitor's IP address
	 * shouldn't be logged, neither as failure nor as redirect hit
	 */
	public function matchesVisitor(): bool
	{
		return $this->matchesIp($this->retour->kirby()->visitor()->ip());
	}

	/**
	 * Returns the patterns from the `ignore` option
	 */
	public function option(): array
	{
		return A::wrap($this->retour->option('ignore'));
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

		$this->retour->config()->update(['ignore' => $patterns]);

		return $patterns;
	}
}
