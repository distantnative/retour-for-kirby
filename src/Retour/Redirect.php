<?php

namespace Kirby\Retour;

use Kirby\Cms\Page;
use Kirby\Cms\Url;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\F;
use Kirby\Http\Idn;
use Kirby\Http\Response;
use Kirby\Toolkit\Obj;
use Kirby\Toolkit\Str;

/**
 * Redirect
 * Single redirect with its properties
 */
class Redirect extends Obj
{
	/**
	 * Class constructor, validates data
	 */
	public function __construct(array $data = [])
	{
		if (empty($data['from'] ?? null) === true) {
			throw new InvalidArgumentException('Route requires path: ' . json_encode($data));
		}

		parent::__construct($data);
	}

	/**
	 * Use redirect pattern as id for object
	 */
	public function id(): string
	{
		/**
		 * Fix for issue #300 (See https://github.com/distantnative/retour-for-kirby/issues/300):
		 *
		 * Depending on the settings, the webserver might not always handle
		 * escaped forward-slashes in the way, which this plugin expects it to.
		 *
		 * This specifically results in a 404 when trying to edit a redirect-entry,
		 * unless the ```AllowEncodedSlashes NoDecode``` is set for the Apache
		 * Server. The problem occurs in relation to nginx.
		 *
		 * Many hosting solutions do not allow customers to change such
		 * settings for the web-server, and so another solution is required.
		 *
		 * So, in order to remedy this problem, we replace forward-slash with the
		 * non-visible ascii-characer "GROUP-SEPARATOR" (Oct: 035, Dec: 29,
		 * Hex: 1D). By using a non-visible chracter we ensure that the id
		 * generation from redirect pattern is always unique.
		 *
		 * Note that this fix include changes to two parts of the plug-in
		 * code-base. In this file, and in src/panel/components/Tabs/RedirectsTab.vue
		 */
		return str_replace('/', "\x1D", $this->from());
	}

	/**
	 * Returns whether the redirect has an active status code
	 */
	public function isActive(): bool
	{
		return $this->status() !== null;
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

	/**
	 * Returns whether the route takes priority
	 * over actual pages
	 */
	public function priority(): bool
	{
		return Str::toType($this->priority ?? 'false', 'bool') === true;
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
		$to        = $this->to() ?? '/';
		$to        = static::toPath($to, $placeholders);
		$extension = F::extension($to);
		$path      = Str::beforeEnd($to, '.' . $extension);
		$page      = $kirby->page($path);
		$code      = $this->status();

		// drafts (and pages inside drafts) must not be
		// rendered for or redirected to by visitors
		if (
			$page?->isDraft() === true ||
			$page?->parents()->findBy('status', 'draft') !== null
		) {
			$page = null;
		}

		// Redirects
		if ($code >= 300 && $code < 400) {
			if ($page) {
				$to = $page->url();

				// support for content representations
				if (empty($extension) === false) {
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
			// Set the right response code
			$kirby->response()->code($code);

			// Return page for other codes or
			// deliver HTTP status code without body
			$response = $page ?? new Response('', 'text/html', $code);
		}

		$retour->log()?->add([
			'path'     => Url::path(),
			'redirect' => $this->from()
		]);

		return $response;
	}

	/**
	 * Returns the integer HTTP status code
	 */
	public function status(): int|null
	{
		// the Panel sends an empty string for no status,
		// hand-edited config files might use `false` or `0`
		if (in_array($this->status, [null, '', false, 0, 'disabled'], true) === true) {
			return null;
		}

		return (int)$this->status;
	}

	public function toArray(): array
	{
		return [
			'from'     => $this->from(),
			'to'       => $this->to(),
			'status'   => $this->status(),
			'priority' => $this->priority(),
			'comment'  => $this->comment(),
			'creator'  => $this->creator(),
			'modifier' => $this->modifier(),
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

	/**
	 * Return route definition for Router
	 */
	public function toRoute(): array|null
	{
		if ($this->isActive() === false) {
			return null;
		}

		$redirect = $this;

		return [
			'pattern' => trim($this->from(), '/'),
			'method'  => 'GET|HEAD',
			'action'  => function (...$placeholders) use ($redirect) {
				return $redirect->resolve($placeholders);
			}
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
