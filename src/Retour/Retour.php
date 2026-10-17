<?php

namespace Kirby\Retour;

use Kirby\Cms\App;

class Retour
{
	protected Config $config;
	protected Ignores|null $ignores = null;
	protected Log|null $log = null;
	protected Redirects $redirects;

	protected static self|null $instance = null;

	public function __construct(
		protected App $kirby
	) {
		$this->config = new Config($this);

		$this->redirects = Redirects::factory(
			$this,
			$this->config->get('redirects') ?? []
		);
	}

	public function config(): Config
	{
		return $this->config;
	}

	public function hasLog(): bool
	{
		return $this->option('logs') !== false;
	}

	public function ignores(): Ignores
	{
		return $this->ignores ??= new Ignores($this);
	}

	public static function instance(App|null $kirby = null): self
	{
		$kirby ??= App::instance();

		if (self::$instance?->kirby() === $kirby) {
			return self::$instance;
		}

		return self::$instance = new self($kirby);
	}

	public function kirby(): App
	{
		return $this->kirby;
	}

	public function log(): Log|null
	{
		if ($this->hasLog() === true) {
			return $this->log ??= new Log($this);
		}

		return null;
	}

	public function option(string $key): mixed
	{
		return $this->kirby->option('distantnative.retour.' . $key);
	}

	public function redirects(): Redirects
	{
		return $this->redirects;
	}

	public static function reset(): void
	{
		self::$instance = null;
	}

	/**
	 * Prefix in front of paths in the Panel, `false` if disabled
	 */
	public function site(): string|false
	{
		$site = $this->option('site');

		return match (true) {
			is_string($site) => $site . '/',
			$site === true   => preg_replace('!^https?://(www\.)?!', '', (string)$this->kirby->url()) . '/',
			default          => false
		};
	}
}
