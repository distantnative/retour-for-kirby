<?php

namespace Kirby\Retour;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;

/**
 * Paths not to log as failures and IP addresses not to log at all
 */
class Ignores
{
	/**
	 * Paths bots commonly probe, mostly of WordPress installations
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
	 * Adds to the stored patterns, so concurrent additions aren't lost
	 *
	 * @throws InvalidArgumentException If a pattern is invalid
	 */
	public function add(string $pattern): array
	{
		$data = $this->retour->config()->update(function (array $data) use ($pattern): array {
			$data['ignore'] = static::normalize([...$data['ignore'] ?? [], $pattern]);
			return $data;
		});

		return $data['ignore'];
	}

	/**
	 * IPv4-mapped IPv6 addresses (`::ffff:192.0.2.1`) in their IPv4 form,
	 * `null` for invalid addresses
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
	 * Patterns managed in the Panel
	 */
	public function custom(): array
	{
		return $this->retour->config()->get('ignore') ?? [];
	}

	/**
	 * `$ip` in binary form, `$entry` an address or a range like `192.0.2.0/24`
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

	public function ips(): array
	{
		return A::wrap($this->retour->option('ignoreIps'));
	}

	public function matches(string $path, array|null $patterns = null): bool
	{
		$patterns ??= [...$this->option(), ...$this->custom()];

		foreach ($patterns as $pattern) {
			// invalid entries from the `ignore` option or empty items in the config file
			if (is_string($pattern) === false || $pattern === '') {
				continue;
			}

			if (Pattern::matches($pattern, $path, 'iu') === true) {
				return true;
			}
		}

		return false;
	}

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
			if (is_string($entry) === false) {
				continue;
			}

			if (static::inRange($ip, trim($entry)) === true) {
				return true;
			}
		}

		return false;
	}

	public function matchesVisitor(): bool
	{
		return $this->matchesIp($this->retour->kirby()->visitor()->ip());
	}

	/**
	 * @throws InvalidArgumentException If a pattern is invalid
	 */
	protected static function normalize(array $patterns): array
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

		return $patterns;
	}

	public function option(): array
	{
		return A::wrap($this->retour->option('ignore'));
	}

	/**
	 * @throws InvalidArgumentException If a pattern is invalid
	 */
	public function set(array $patterns): array
	{
		$patterns = static::normalize($patterns);
		$this->retour->config()->update(['ignore' => $patterns]);
		return $patterns;
	}
}
