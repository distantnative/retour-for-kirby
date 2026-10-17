<?php

namespace Kirby\Retour;

use Closure;
use Kirby\Data\Data;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\F;
use Throwable;

class Config
{
	protected array $data;
	protected LogicException|null $error = null;

	public function __construct(
		protected Retour $retour
	) {
		$this->read();
	}

	/**
	 * @throws LogicException If the contents cannot be parsed
	 */
	protected static function decode(string $contents, string $file): array
	{
		if (trim($contents) === '') {
			return [];
		}

		try {
			return Data::decode($contents, F::extension($file));
		} catch (Throwable $error) {
			throw new LogicException(
				message: 'Retour: The config file could not be read: ' . $error->getMessage(),
				previous: $error
			);
		}
	}

	public function error(): LogicException|null
	{
		return $this->error;
	}

	public function get(string $key): mixed
	{
		return $this->data[$key] ?? null;
	}

	/**
	 * Shared lock, as `F::update()` empties the file before writing
	 *
	 * @throws LogicException If the file cannot be read
	 */
	protected static function load(string $file): string
	{
		$handle = @fopen($file, 'r');

		if ($handle === false) {
			throw new LogicException(
				message: 'Retour: The config file could not be read: ' . $file
			);
		}

		try {
			flock($handle, LOCK_SH);
			$contents = stream_get_contents($handle);
		} finally {
			fclose($handle);
		}

		if ($contents === false) {
			// @codeCoverageIgnoreStart
			throw new LogicException(
				message: 'Retour: The config file could not be read: ' . $file
			);
			// @codeCoverageIgnoreEnd
		}

		return $contents;
	}

	/**
	 * Cached by hash, as parsing many redirects on every request is slow
	 *
	 * @throws LogicException If the contents cannot be parsed
	 */
	protected function parse(string $contents, string $file): array
	{
		$cache = $this->retour->kirby()->cache('distantnative.retour');
		$key   = 'config-' . md5($file);
		$hash  = hash('xxh128', $contents);
		$entry = $cache->get($key);

		if (
			($entry['hash'] ?? null) === $hash &&
			is_array($entry['data'] ?? null) === true
		) {
			return $entry['data'];
		}

		$data = static::decode($contents, $file);

		$cache->set($key, [
			'hash' => $hash,
			'data' => $data
		]);

		return $data;
	}

	/**
	 * An unreadable file means no redirects, but it is never overwritten
	 */
	public function read(): array
	{
		$file        = $this->root();
		$this->error = null;

		if (F::exists($file) === false) {
			return $this->data = [];
		}

		try {
			return $this->data = $this->parse(static::load($file), $file);
		} catch (LogicException $error) {
			$this->error = $error;
			return $this->data = [];
		}
	}

	public function root(): string
	{
		$root = $this->retour->option('config');

		if ($root instanceof Closure) {
			$root = $root();
		}

		return $root;
	}

	/**
	 * Merges the keys or replaces the data with the closure's result;
	 * locked to keep concurrent changes, never overwrites unparsable files
	 *
	 * @param array|Closure(array $data): array $data
	 * @throws LogicException If the config file could not be read or written
	 */
	public function update(array|Closure $data): array
	{
		$file = $this->root();
		$new  = [];

		$updated = F::update($file, function (string $contents) use ($file, $data, &$new): string {
			$current = static::decode($contents, $file);

			if ($data instanceof Closure) {
				$new = $data($current);
			} else {
				$new = [...$current, ...$data];
			}

			return Data::encode($new, F::extension($file));
		});

		if ($updated === false) {
			// @codeCoverageIgnoreStart
			throw new LogicException(
				message: 'Retour: writing config file failed'
			);
			// @codeCoverageIgnoreEnd
		}

		$this->error = null;
		return $this->data = $new;
	}
}
