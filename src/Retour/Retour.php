<?php

namespace Kirby\Retour;

use Kirby\Cms\App;
use Kirby\Http\Route;

/**
 * Main plugin class responsible for general tasks
 *
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */
class Retour
{
	public static $instance;

	protected App $kirby;
	protected Config $config;
	protected Redirects $redirects;

	/**
	 * Instance for accessing the log database
	 * or a mockup for when this feature is disabled
	 */
	protected Log|LogDisabled|null $log = null;

	public function __construct(App|null $kirby = null)
	{
		$this->kirby  = $kirby ?? App::instance();
		$this->config = new Config($this);

		// initialize redirects
		$this->redirects = Redirects::factory(
			$this,
			$this->config->data('redirects') ?? []
		);
	}

	public function config(): Config
	{
		return $this->config;
	}

	/**
	 * Returns if log feature is activated
	 */
	public function hasLog(): bool
	{
		return $this->option('logs', true) !== false;
	}

	public function ignore(string $path): bool
	{
		// temporary route for regex matching
		$route  = new Route($path, 'GET', fn () => null);
		$ignore = $this->option('ignore', []);
		$ignore = $route->regex(implode('|', $ignore));

		if (preg_match('!^(' . $ignore . ')$!i', $path) === 1) {
			return true;
		}

		return false;
	}

	/**
	 * Returns the singleton plugin instance
	 */
	public static function instance(App|null $kirby = null): self
	{
		if (
			self::$instance !== null &&
			($kirby === null || self::$instance->kirby() === $kirby)
		) {
			return self::$instance;
		}

		return self::$instance = new self($kirby);
	}

	public function kirby(): App
	{
		return $this->kirby;
	}

	/**
	 * Returns a log instance
	 */
	public function log(): Log|LogDisabled
	{
		// log feature disabled, return dummy class
		if ($this->hasLog() === false) {
			return $this->log ??= new LogDisabled();
		}

		return $this->log ??= new Log($this);
	}

	/**
	 * Returns a plugin option value
	 */
	public function option(string $key, mixed $default = null): mixed
	{
		return $this->kirby()->option('distantnative.retour.' . $key, $default);
	}

	/**
	 * Returns the Redirects instance
	 */
	public function redirects(): Redirects
	{
		return $this->redirects;
	}

	/**
	 * Returns domain for site
	 */
	public function site(): string|false
	{
		$site = $this->option('site', true);

		if (is_string($site) === true) {
			return $site . '/';
		}

		if ($site === true) {
			$url = (string)kirby()->url();
			return preg_replace('$^(http(s)?\:\/\/(www\.)?)$', '', $url) . '/';
		}

		return false;
	}
}
