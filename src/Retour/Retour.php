<?php

namespace Kirby\Retour;

use Kirby\Cms\App;
use Kirby\Http\Route;

/**
 * Main plugin class responsible for general tasks
 */
class Retour
{
	protected Config $config;
	protected static self|null $instance = null;
	protected App $kirby;

	/**
	 * Instance for accessing the log database
	 * or a mockup for when this feature is disabled
	 */
	protected Log|LogDisabled|null $log = null;
	protected Redirects $redirects;

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
		$patterns = $this->option('ignore', []);

		if ($patterns === []) {
			return false;
		}

		// temporary route for regex matching
		$route   = new Route($path, 'GET', fn () => null);
		$pattern = $route->regex(implode('|', $patterns));
		return preg_match('!^(' . $pattern . ')$!i', $path) === 1;
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
		return $this->kirby->option('distantnative.retour.' . $key, $default);
	}

	/**
	 * Returns the Redirects instance
	 */
	public function redirects(): Redirects
	{
		return $this->redirects;
	}

	public static function reset(): void
	{
		self::$instance = null;
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
			$url = (string)$this->kirby->url();
			return preg_replace('!^https?://(www\.)?!', '', $url) . '/';
		}

		return false;
	}
}
