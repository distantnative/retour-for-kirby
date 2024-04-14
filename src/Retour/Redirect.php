<?php

namespace Kirby\Retour;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use Kirby\Http\Header;
use Kirby\Http\Response;
use Kirby\Http\Url;
use Kirby\Toolkit\Obj;
use Kirby\Toolkit\Str;

/**
 * Redirect
 * Single redirect with its properties
 *
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
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
	 * Returns whether the route is enabled
	 * with status code
	 */
	public function isActive(): bool
	{
		return $this->status() !== null;
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
	 * Returns the integer HTTP status code
	 */
	public function status(): int|null
	{
		if (in_array($this->status ?? null, [null, 'disabled']) == true) {
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
	public function toRoute(): array|false
	{
		if ($this->isActive() === false) {
			return false;
		}

		$redirect = $this;

		return [
			'pattern' => trim($this->from(), '/'),
			'action'  => function (...$placeholders) use ($redirect) {
				$retour    = Retour::instance();
				$kirby     = $retour->kirby();
				$to        = $redirect->to() ?? '/';
				$to        = Redirect::toPath($to, $placeholders);
				$extension = F::extension($to);
				$path      = Str::beforeEnd($to, '.' . $extension);
				$page      = $kirby->page($path);
				$code      = $redirect->status();

				// Add log entry
				$retour->log()->add([
					'path'     => Url::path(),
					'redirect' => $redirect->from()
				]);

				// Redirects
				// @codeCoverageIgnoreStart
				if ($code >= 300 && $code < 400) {
					if ($page) {
						$to = $page->url();

						// support for content representations
						if (empty($extension) === false) {
							$to .= '.' . $extension;
						}
					}

					Response::go($to, $code);
				}
				// @codeCoverageIgnoreEnd

				// Set the right response code
				$kirby->response()->code($code);

				// Return page for other codes
				if ($page) {
					return $page;
				}

				// Deliver HTTP status code and die
				// @codeCoverageIgnoreStart
				Header::status($code);
				die();
				// @codeCoverageIgnoreEnd
			}
		];
	}
}
