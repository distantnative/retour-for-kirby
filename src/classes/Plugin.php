<?php

namespace distantnative\Retour;

use Kirby\Cms\App;

/**
 * Plugin
 * Main plugin class responsible for general tasks
 *
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */
class Plugin
{
    /**
     * Singleton plugin instance
     */
    protected static $instance;

    /**
     * Kirby App instance
     */
    protected App $kirby;

    /**
     * Instance for accessing the log database
     * or a mockup for when this feature is disabled
     */
    protected Log|LogDisabled|null $log = null;

    /**
     * Instnace for accessing all configures redirects
     */
    protected Redirects $redirects;


    /**
     * Class constructor
     */
    public function __construct(App|null $kirby = null)
    {
        $this->kirby = $kirby ?? App::instance();

        // load config
        $config = $this->config();

        // initialize redirects
        $this->redirects = Redirects::factory(
            $this,
            $config['redirects'] ?? []
        );
    }

    /**
     * Initalizes the Config silo
     */
    public function config(): array
    {
        // get path to config file
        $default = $this->kirby()->root('config') . '/retour.yml';
        $path    = $this->option('config', $default);

        // load config into silo
        return Config::load($path);
    }

    /**
     * Returns if log feature is activated
     */
    public function hasLog(): bool
    {
        return $this->option('logs', true) !== false;
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

    /**
     * Returns the Kirby App instance
     */
    public function kirby(): App
    {
        return $this->kirby;
    }

    /**
     * Returns a log instance
     */
    public function log(): Log|LogDisabled
    {
        // log instance already exists
        if ($this->log !== null) {
            return $this->log;
        }

        // log feature disabled, return dummy class
        if ($this->hasLog() === false) {
            return $this->log = new LogDisabled();
        }

        return $this->log = new Log($this);
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
     * Resets the singleton plugin instance
     */
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
            $url = (string)kirby()->url();
            return preg_replace('$^(http(s)?\:\/\/(www\.)?)$', '', $url) . '/';
        }

       return false;
    }
}
