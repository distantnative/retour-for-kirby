<?php

namespace Kirby\Retour;

use Kirby\Data\Data;
use Kirby\Exception\LogicException;
use Throwable;

/**
 * Handles reading from/writing to the config file
 *
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */
class Config
{
	protected array $data;

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

	public function file(): string
	{
		$retour  = $this->retour();
		$default = $retour->kirby()->root('config') . '/retour.yml';
		$path    = $retour->option('config', $default);

		if (is_callable($path) === true) {
			$path = $path();
		}

		return $path;
	}

	public function read(): array
	{
		try {
			$file = $this->file();
			return $this->data = Data::read($file);
		} catch (Throwable) {
			return $this->data =  [];
		}
	}

	public function retour(): Retour
	{
		return $this->retour;
	}

	public function write(
		string|array $key,
		mixed $value = null
	): array {
		$data = $this->data;
		if (is_array($key) === true) {
			$data = array_merge($data, $key);
		} else {
			$data[$key] = $value;
		}

		if (Data::write($this->file(), $data) === true) {
			return $this->data = $data;
		}

		// @codeCoverageIgnoreStart
		throw new LogicException('Retour: writing config file failed');
		// @codeCoverageIgnoreEnd
	}
}
