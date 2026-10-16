<?php

namespace Kirby\Retour;

use Kirby\Data\Data;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\F;
use Throwable;

/**
 * Handles reading from/writing to the config file
 */
class Config
{
	protected array $data;
	protected LogicException|null $error = null;

	public function __construct(
		protected Retour $retour
	) {
		$this->read();
	}

	public function data(string|null $key = null): mixed
	{
		if ($key === null) {
			return $this->data;
		}

		return $this->data[$key] ?? null;
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

	/**
	 * Returns the error if the config file could not be read
	 */
	public function error(): LogicException|null
	{
		return $this->error;
	}

	public function file(): string
	{
		$default = $this->retour->kirby()->root('config') . '/retour.yml';
		$path    = $this->retour->option('config', $default);

		if (is_callable($path) === true) {
			$path = $path();
		}

		return $path;
	}

	/**
	 * Reads the file under a shared lock, as `F::update()`
	 * empties the file before writing the new contents
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
	 * Reads the config file; if it cannot be read, the site keeps
	 * running without redirects, but the file won't be overwritten
	 */
	public function read(): array
	{
		$file        = $this->file();
		$this->error = null;

		if (F::exists($file) === false) {
			return $this->data = [];
		}

		try {
			return $this->data = static::decode(static::load($file), $file);
		} catch (LogicException $error) {
			$this->error = $error;
			return $this->data = [];
		}
	}

	/**
	 * Updates the config file under an exclusive lock, based on its
	 * current contents: concurrent changes of other keys are kept and
	 * a file that cannot be parsed (anymore) is never overwritten
	 *
	 * @throws LogicException If the config file could not be read or written
	 */
	public function write(
		string|array $key,
		mixed $value = null
	): array {
		$file = $this->file();
		$data = [];

		$updated = F::update($file, function (string $contents) use ($file, $key, $value, &$data): string {
			$data = static::decode($contents, $file);

			if (is_array($key) === true) {
				$data = array_merge($data, $key);
			} else {
				$data[$key] = $value;
			}

			return Data::encode($data, F::extension($file));
		});

		if ($updated === false) {
			// @codeCoverageIgnoreStart
			throw new LogicException(
				message: 'Retour: writing config file failed'
			);
			// @codeCoverageIgnoreEnd
		}

		$this->error = null;
		return $this->data = $data;
	}
}
