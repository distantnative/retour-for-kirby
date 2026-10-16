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
	protected string|null $comment;
	protected string|null $creator;
	protected string $from;
	protected string|null $modifier;
	protected bool $priority;
	protected int|null $status;
	protected string|null $to;

	public function __construct(array $data)
	{
		if (in_array($data['from'] ?? null, [null, ''], true) === true) {
			throw new InvalidArgumentException(
				message: 'Route requires path: ' . json_encode($data)
			);
		}

		$this->from     = $data['from'];
		$this->to       = $data['to'] ?? null;
		$this->status   = static::normalizeStatus($data['status'] ?? null);
		$this->priority = Str::toType($data['priority'] ?? 'false', 'bool') === true;
		$this->comment  = $data['comment'] ?? null;
		$this->creator  = $data['creator'] ?? null;
		$this->modifier = $data['modifier'] ?? null;
	}

	public function comment(): string|null
	{
		return $this->comment;
	}

	public function creator(): string|null
	{
		return $this->creator;
	}

	public function from(): string
	{
		return $this->from;
	}

	/**
	 * Returns the path pattern as id; webservers often don't handle encoded
	 * slashes (https://github.com/distantnative/retour-for-kirby/issues/300),
	 * so "/" is swapped for the invisible GROUP SEPARATOR (\x1D); reverted in
	 * `Panel\Id::toPath()`, mirrored in src/panel/components/Views/CollectionView.vue
	 */
	public function id(): string
	{
		return str_replace('/', "\x1D", $this->from);
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
	 * hand-edited config files might use `false` or `0`
	 */
	protected static function normalizeStatus(mixed $status): int|null
	{
		if (in_array($status, [null, '', false, 0, 'disabled'], true) === true) {
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
	 * @return Page|Response|null `null` if the target page or file doesn't exist anymore
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
			$kirby->response()->code($code);
			$response = $page ?? new Response('', 'text/html', $code);
		}

		// only now, as redirects to deleted pages count as failures
		$retour->log()?->add([
			'path'     => Url::path(),
			'redirect' => $this->from
		]);

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
