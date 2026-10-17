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
 * The order of the redirects defines the precedence of their routes
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
	 * Applies the change to the stored redirects, so concurrent changes aren't lost
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

	public static function factory(Retour $retour, array $config): self
	{
		$redirects = new self($retour, []);

		foreach ($config as $data) {
			$redirect = Redirect::factory($data);
			$redirects->append($redirect->id(), $redirect);
		}

		return $redirects;
	}

	public function toArray(Closure|null $map = null): array
	{
		return $this->values($map ?? fn (Redirect $redirect) => $redirect->toArray());
	}

	/**
	 * Redirects with their hits from the log
	 */
	public function toData(string $from, string $to): array
	{
		$hits = $this->retour->log()?->redirects($from, $to);

		if ($hits !== null) {
			return $this->toArray(fn (Redirect $redirect): array => [
				...$redirect->toArray(),
				'hits' => $hits[$redirect->from()]['hits'] ?? 0,
				'last' => $hits[$redirect->from()]['last'] ?? null
			]);
		}

		return $this->toArray();
	}

	/**
	 * Only the routes that can match the path, as the
	 * router compares paths without `(` as plain strings
	 */
	public function toRoutes(string $path, bool $priority = false): array
	{
		$path   = trim($path, '/');
		$routes = [];

		foreach ($this->data as $redirect) {
			if (
				$redirect->isActive() === true &&
				$redirect->priority() === $priority &&
				(
					Pattern::hasPlaceholder($redirect->from()) === true ||
					trim($redirect->from(), '/') === $path
				)
			) {
				$routes[] = $redirect->toRoute();
			}
		}

		return $routes;
	}

	/**
	 * Keeps the position of the redirect
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
	 * An invalid path pattern would break routing for every request
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
