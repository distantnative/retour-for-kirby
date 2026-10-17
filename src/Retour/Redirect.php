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

/**
 * Single redirect with its properties
 */
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
	 * Creates a redirect from config or form data, which can
	 * have numbers as strings (or paths as numbers), `"true"`
	 * for priority and `"disabled"` or `0` for no status code
	 *
	 * @throws InvalidArgumentException If the path is missing
	 */
	public static function factory(array $data): static
	{
		return new static(
			from: $data['from'] ?? '',
			to: $data['to'] ?? null,
			status: static::normalizeStatus($data['status'] ?? null),
			priority: Str::toType($data['priority'] ?? 'false', 'bool') === true,
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

	/**
	 * Returns whether the redirect has an active status code
	 */
	public function isActive(): bool
	{
		return $this->status !== null;
	}

	/**
	 * Encodes an internationalized domain of the URL without
	 * rebuilding the whole URL like `Response::redirect()` does,
	 * which would re-encode the query string
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
	 * Returns whether the route takes priority
	 * over actual pages
	 */
	public function priority(): bool
	{
		return $this->priority;
	}

	/**
	 * Resolves the redirect action for the router
	 *
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

		// drafts (and pages inside drafts) must not be
		// rendered for or redirected to by visitors
		if (
			$page?->isDraft() === true ||
			$page?->parents()->findBy('status', 'draft') !== null
		) {
			$page = null;
		}

		if ($code >= 300 && $code < 400) {
			if ($page) {
				$to = $page->url();

				// support for content representations
				if ($extension !== '') {
					// the home page's URL is the site URL, but its
					// representations are only reachable via its slug
					if ($page->isHomePage() === true) {
						$to .= '/' . $page->slug();
					}

					$to .= '.' . $extension;
				}
			} else {
				try {
					// resolve paths (and UUIDs) against the site URL,
					// e.g. for sites in a subfolder; absolute URLs are kept
					$to = Url::to($to);
				} catch (NotFoundException) {
					// the target page or file has been deleted,
					// so the request is handled as failure
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
			// an empty page would look like a successful request,
			// so it's handled as failure (other codes like `410`
			// can be meant to answer without a page)
			if ($page === null && $code === 200) {
				return null;
			}

			$kirby->response()->code($code);
			$response = $page ?? new Response('', 'text/html', $code);
		}

		// only now, as redirects to deleted pages count as failures;
		// HEAD requests (e.g. by uptime monitors) would inflate the stats,
		// requests from ignored IP addresses aren't logged at all
		if (
			$kirby->request()->method() !== 'HEAD' &&
			$retour->ignores()->matchesVisitor() === false
		) {
			$retour->log()?->add(['redirect' => $this->from]);
		}

		return $response;
	}

	/**
	 * Returns the HTTP status code or `null` if disabled
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
	 * Replaces placeholders in $path string
	 *
	 * @param array<int, string> $placeholders
	 */
	public static function toPath(
		string $path,
		array $placeholders = []
	): string {
		// Replace alias for home
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
	 * Appends the query string of the current request to the
	 * destination URL, merging it with an existing query string
	 * if present. The query string is passed on as sent, as
	 * re-encoding it would break tracking parameters like
	 * Google's `_gl=1*abc*_ga*…`
	 */
	public static function withQuery(string $to): string
	{
		$kirby = Retour::instance()->kirby();
		$uri   = $kirby->environment()->get('REQUEST_URI') ?? '';
		$query = Str::after($uri, '?');

		if ($query === '') {
			return $to;
		}

		// keep the fragment at the end, otherwise
		// the query string would become part of it
		[$url, $fragment] = array_pad(explode('#', $to, 2), 2, null);

		$url .= (str_contains($url, '?') ? '&' : '?') . $query;

		if ($fragment !== null) {
			$url .= '#' . $fragment;
		}

		return $url;
	}
}
