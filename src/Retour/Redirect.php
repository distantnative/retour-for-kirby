<?php

namespace Kirby\Retour;

use Kirby\Cms\Page;
use Kirby\Cms\Url;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\F;
use Kirby\Http\Idn;
use Kirby\Http\Response;
use Kirby\Toolkit\Str;

class Redirect
{
	/**
	 * @throws InvalidArgumentException If the path is empty
	 */
	public function __construct(
		protected string $from,
		protected string|null $to = null,
		protected int|null $status = null,
		protected bool $priority = false,
		protected string|null $comment = null,
		protected string|null $creator = null,
		protected string|null $modifier = null
	) {
		if ($from === '') {
			throw new InvalidArgumentException(
				message: 'Route requires path'
			);
		}
	}

	public function comment(): string|null
	{
		return $this->comment;
	}

	public function creator(): string|null
	{
		return $this->creator;
	}

	/**
	 * Config and form data can have numbers as strings
	 * (or paths as numbers) and `"true"` for priority
	 *
	 * @throws InvalidArgumentException If the path is missing
	 */
	public static function factory(array $data): static
	{
		return new static(
			from: $data['from'] ?? '',
			to: $data['to'] ?? null,
			status: static::normalizeStatus($data['status'] ?? null),
			priority: filter_var($data['priority'] ?? false, FILTER_VALIDATE_BOOLEAN),
			comment: $data['comment'] ?? null,
			creator: $data['creator'] ?? null,
			modifier: $data['modifier'] ?? null
		);
	}

	public function from(): string
	{
		return $this->from;
	}

	public function id(): string
	{
		return $this->from;
	}

	public function isActive(): bool
	{
		return $this->status !== null;
	}

	/**
	 * Encodes an IDN host without rebuilding the URL like
	 * `Response::redirect()`, which would re-encode the query string
	 */
	protected static function location(string $url): string
	{
		$host = parse_url($url, PHP_URL_HOST);

		if (is_string($host) === false || $host === '') {
			return $url;
		}

		$encoded = Idn::encode($host);

		if ($encoded === false || $encoded === $host) {
			return $url;
		}

		return substr_replace($url, $encoded, strpos($url, $host), strlen($host));
	}

	public function modifier(): string|null
	{
		return $this->modifier;
	}

	/**
	 * The Panel sends an empty string for no status,
	 * hand-edited config files might use `false`, `true` or `0`
	 */
	protected static function normalizeStatus(mixed $status): int|null
	{
		if (in_array($status, [null, '', false, true, 0, '0', 'disabled'], true) === true) {
			return null;
		}

		return (int)$status;
	}

	/**
	 * Whether the redirect takes priority over pages
	 */
	public function priority(): bool
	{
		return $this->priority;
	}

	/**
	 * @param array<int, string> $placeholders
	 * @return Page|Response|null `null` if the target page or file doesn't exist (anymore)
	 */
	public function resolve(array $placeholders): Page|Response|null
	{
		$retour    = Retour::instance();
		$kirby     = $retour->kirby();
		$to        = static::toPath($this->to ?? '/', $placeholders);
		$extension = F::extension($to);
		$path      = Str::beforeEnd($to, '.' . $extension);
		$page      = $kirby->page($path);
		$code      = $this->status;

		// visitors must not get drafts (or pages inside drafts)
		if (
			$page?->isDraft() === true ||
			$page?->parents()->findBy('status', 'draft') !== null
		) {
			$page = null;
		}

		if ($code >= 300 && $code < 400) {
			if ($page) {
				$to = $page->url();

				if ($extension !== '') {
					// the home page's representations are only reachable via its slug
					if ($page->isHomePage() === true) {
						$to .= '/' . $page->slug();
					}

					$to .= '.' . $extension;
				}
			} else {
				try {
					// resolves paths and UUIDs against the site URL (e.g. in a subfolder)
					$to = Url::to($to);
				} catch (NotFoundException) {
					// the target page or file has been deleted, handled as failure
					return null;
				}
			}

			if ($retour->option('preserveQuery') === true) {
				$to = static::withQuery($to);
			}

			$response = new Response([
				'code'    => $code,
				'headers' => ['Location' => static::location($to)]
			]);
		} else {
			// an empty 200 would look like a success, unlike e.g. an empty `410`
			if ($page === null && $code === 200) {
				return null;
			}

			$kirby->response()->code($code);
			$response = $page ?? new Response('', 'text/html', $code);
		}

		// only now, as redirects to deleted pages count as failures;
		// HEAD requests (e.g. by uptime monitors) would inflate the stats
		if (
			$kirby->request()->method() !== 'HEAD' &&
			$retour->ignores()->matchesVisitor() === false
		) {
			$retour->log()?->add(['redirect' => $this->from]);
		}

		return $response;
	}

	/**
	 * `null` if disabled
	 */
	public function status(): int|null
	{
		return $this->status;
	}

	public function to(): string|null
	{
		return $this->to;
	}

	public function toArray(): array
	{
		return [
			'from'     => $this->from,
			'to'       => $this->to,
			'status'   => $this->status,
			'priority' => $this->priority,
			'comment'  => $this->comment,
			'creator'  => $this->creator,
			'modifier' => $this->modifier,
		];
	}

	/**
	 * Replaces `$1`, `$2`, … in the path with the placeholders
	 *
	 * @param array<int, string> $placeholders
	 */
	public static function toPath(
		string $path,
		array $placeholders = []
	): string {
		if ($path === '/') {
			return 'home';
		}

		foreach ($placeholders as $i => $placeholder) {
			$path = str_replace('$' . ($i + 1), $placeholder, $path);
		}

		return $path;
	}

	public function toRoute(): array
	{
		// the router binds `$this` of the action to the route
		$redirect = $this;

		return [
			'pattern' => trim($this->from, '/'),
			'method'  => 'GET|HEAD',
			'action'  => fn (...$placeholders) => $redirect->resolve($placeholders)
		];
	}

	/**
	 * Appends the request's query string as sent, as re-encoding it
	 * would break tracking parameters like Google's `_gl=1*abc*_ga*…`
	 */
	public static function withQuery(string $to): string
	{
		$uri   = Retour::instance()->kirby()->environment()->get('REQUEST_URI') ?? '';
		$query = Str::after($uri, '?');

		if ($query === '') {
			return $to;
		}

		// keep the fragment last, or the query would become part of it
		[$url, $fragment] = array_pad(explode('#', $to, 2), 2, null);

		$url .= (str_contains($url, '?') ? '&' : '?') . $query;

		if ($fragment !== null) {
			$url .= '#' . $fragment;
		}

		return $url;
	}
}
