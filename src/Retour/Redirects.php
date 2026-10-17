<?php

namespace Kirby\Retour;

use Closure;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Header;
use Kirby\Toolkit\Collection;

/**
 * Collection of all configured redirects;
 * their order defines the precedence of the routes
 */
class Redirects extends Collection
{
	public function __construct(
		protected Retour $retour,
		array $redirects
	) {
		parent::__construct($redirects, caseSensitive: true);
	}

	/**
	 * Applies the change to the redirects as currently stored
	 * and saves them, so concurrent changes aren't lost
	 *
	 * @throws LogicException If the config file could not be read or written
	 */
	protected function commit(Closure $change): void
	{
		$this->retour->config()->update(function (array $data) use ($change): array {
			$this->data = static::factory($this->retour, $data['redirects'] ?? [])->data;
			$change();
			$data['redirects'] = $this->toArray();
			return $data;
		});
	}

	/**
	 * Adds a new redirect with the highest precedence
	 *
	 * @throws DuplicateException If a redirect with the same path exists
	 * @throws InvalidArgumentException If the path pattern or status code is invalid
	 */
	public function create(array $data): Redirect
	{
		$redirect = Redirect::factory($data);

		static::validate($redirect);

		$this->commit(function () use ($redirect) {
			if ($this->has($redirect->id()) === true) {
				throw new DuplicateException(
					message: 'Redirect with ID already exists'
				);
			}

			$this->prepend($redirect->id(), $redirect);
		});

		return $redirect;
	}

	public function delete(string $id): bool
	{
		$this->commit(fn () => $this->remove($id));
		return true;
	}

	/**
	 * Creates the collection from the config data
	 */
	public static function factory(Retour $retour, array $config): self
	{
		$redirects = new self($retour, []);

		foreach ($config as $data) {
			$redirect = Redirect::factory($data);
			$redirects->append($redirect->id(), $redirect);
		}

		return $redirects;
	}

	/**
	 * Returns a list of the redirects as arrays (or mapped by `$map`)
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
		$log = $this->retour->log();

		if ($log === null) {
			return $this->toArray();
		}

		// get hits of all redirects at once
		$hits = $log->redirects($from, $to);

		return $this->toArray(function (Redirect $redirect) use ($hits): array {
			$data         = $redirect->toArray();
			$data['hits'] = $hits[$data['from']]['hits'] ?? 0;
			$data['last'] = $hits[$data['from']]['last'] ?? null;
			return $data;
		});
	}

	/**
	 * Returns the routes of all active redirects
	 * with or without priority over pages
	 */
	public function toRoutes(bool $priority = false): array
	{
		$redirects = $this->filter(
			fn (Redirect $redirect): bool =>
				$redirect->isActive() === true &&
				$redirect->priority() === $priority
		);

		return $redirects->toArray(
			fn (Redirect $route) => $route->toRoute()
		);
	}

	/**
	 * Updates a redirect with the data while keeping its position
	 *
	 * @throws NotFoundException If the redirect doesn't exist
	 * @throws DuplicateException If another redirect already has the new path
	 * @throws InvalidArgumentException If the path pattern or status code is invalid
	 */
	public function update(string $id, array $data): Redirect
	{
		$redirect = null;

		$this->commit(function () use ($id, $data, &$redirect) {
			$current  = $this->get($id) ?? throw new NotFoundException(
				message: 'Redirect not found'
			);
			$redirect = Redirect::factory([...$current->toArray(), ...$data]);

			if ($redirect->id() !== $id && $this->has($redirect->id()) === true) {
				throw new DuplicateException(
					message: 'Redirect with ID already exists'
				);
			}

			static::validate($redirect);

			$redirects = [];

			foreach ($this->data as $key => $item) {
				// numeric IDs become integer array keys
				if ((string)$key === $id) {
					$key  = $redirect->id();
					$item = $redirect;
				}

				$redirects[$key] = $item;
			}

			$this->data = $redirects;
		});

		return $redirect;
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
			Pattern::hasPlaceholder($from) === true &&
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
