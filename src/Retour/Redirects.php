<?php

namespace Kirby\Retour;

use Closure;
use Kirby\Cms\Collection;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Header;

/**
 * Redirects
 * Collection of all configured redirect routes
 */
class Redirects extends Collection
{
	public function __construct(
		protected Retour $retour,
		array $redirects
	) {
		parent::__construct($redirects);
	}

	/**
	 * Creates new redirect
	 */
	public function create(array $data): self
	{
		$redirect = new Redirect($data);

		if ($this->has($redirect->id()) === true) {
			throw new DuplicateException('Redirect with ID already exists');
		}

		static::validate($redirect);

		$this->prepend($redirect->id(), $redirect);
		return $this;
	}

	/**
	 * Takes a config array and turns it into
	 * a collection of redirect objects
	 */
	public static function factory(Retour $retour, array $config): self
	{
		$redirects = new self($retour, []);

		foreach ($config as $data) {
			$redirect = new Redirect($data);
			$redirects->append($redirect->id(), $redirect);
		}

		return $redirects;
	}

	public function retour(): Retour
	{
		return $this->retour;
	}

	/**
	 * Writes collection to config file
	 */
	public function save(): void
	{
		$config = $this->retour->config();
		$config->write('redirects', $this->toArray());
	}

	/**
	 * Turns collection into array, by default turning
	 * Redirect objects into array as well
	 */
	public function toArray(Closure|null $map = null): array
	{
		$map ??= fn (Redirect $redirect) => $redirect->toArray();
		$array = parent::toArray($map);
		return array_values($array);
	}

	/**
	 * Returns redirects data combined with log data
	 */
	public function toData(string $from, string $to): array
	{
		$retour = $this->retour;

		// If logging is disabled, return without data
		if ($retour->hasLog() === false) {
			return $this->toArray();
		}

		// get hits of all redirects at once
		$hits = $retour->log()->redirects($from, $to);

		return $this->toArray(function (Redirect $redirect) use ($hits): array {
			$data         = $redirect->toArray();
			$data['hits'] = $hits[$data['from']]['hits'] ?? 0;
			$data['last'] = $hits[$data['from']]['last'] ?? null;
			return $data;
		});
	}

	/**
	 * Returns routes config for all active redirects
	 */
	public function toRoutes(bool $priority = false): array
	{
		// Filter: only active redirects with matching priority
		$redirects = $this->filter(
			fn (Redirect $redirect): bool =>
				$redirect->isActive() === true &&
				$redirect->priority() === $priority
		);

		// create route array for each redirect
		return $redirects->toArray(
			fn (Redirect $route) => $route->toRoute()
		);
	}

	/**
	 * Replaces a redirect while keeping its position,
	 * as the order defines the precedence of the routes
	 *
	 * @param string|Redirect $key ID of the redirect to replace
	 * @param array|Redirect $data
	 * @throws NotFoundException If the redirect doesn't exist
	 * @throws DuplicateException If another redirect already has the new ID
	 * @throws InvalidArgumentException If the path pattern or status code is invalid
	 */
	public function update(string|object $key, mixed $data = null): static
	{
		if (is_object($key) === true) {
			return $this->update($key->id(), $key);
		}

		$redirect = $data instanceof Redirect ? $data : new Redirect($data);
		$id       = $redirect->id();

		if ($this->has($key) === false) {
			throw new NotFoundException(
				message: 'Redirect not found'
			);
		}

		if ($id !== $key && $this->has($id) === true) {
			throw new DuplicateException(
				message: 'Redirect with ID already exists'
			);
		}

		static::validate($redirect);

		$data = [];

		foreach ($this->data as $current => $item) {
			// numeric IDs become integer array keys
			if ((string)$current === $key) {
				$current = $id;
				$item    = $redirect;
			}

			$data[$current] = $item;
		}

		$this->data = $data;
		return $this;
	}

	/**
	 * Ensures that the redirect can be routed when saving it;
	 * an invalid path pattern would break routing for every request
	 *
	 * @throws InvalidArgumentException If the path pattern or status code is invalid
	 */
	protected static function validate(Redirect $redirect): void
	{
		$from = $redirect->from();

		if (
			Pattern::isLiteral($from) === false &&
			Pattern::isValid($from) === false
		) {
			throw new InvalidArgumentException(
				message: 'Invalid path pattern: ' . $from
			);
		}

		$status = $redirect->status();

		if (
			$status !== null &&
			array_key_exists('_' . $status, Header::$codes) === false
		) {
			throw new InvalidArgumentException(
				message: 'Invalid status code: ' . $status
			);
		}
	}
}
