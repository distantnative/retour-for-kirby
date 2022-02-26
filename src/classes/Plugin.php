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
     *
     * @var self|null
     */
    protected static $instance;

    /**
     * Kirby App instance
     *
     * @var \Kirby\Cms\App
     */
    protected App $kirby;

    /**
     * Instance for accessing the log database
     * or a mockup for when this feature is disabled
     *
     * @var \distantnative\Retour\Log|\distantnative\Retour\LogDisabled|null
     */
    protected $log;

    /**
     * Instnace for accessing all configures redirects
     *
     * @var \distantnative\Retour\Redirects
     */
    protected Redirects $redirects;


    /**
     * Class constructor
     *
     * @param \Kirby\Cms\App|null $kirby Kirby App instance
     */
    public function __construct(?App $kirby = null)
    {
        $this->kirby = $kirby ?? kirby();

        // load config
        $config = $this->config();

        // initialize redirects
        $redirects = $config['redirects'] ?? [];
        $this->redirects = Redirects::factory($this, $redirects);
    }

    /**
     * Initalizes the Config silo
     *
     * @return array
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
     *
     * @return bool
     */
    public function hasLog(): bool
    {
        return $this->option('logs', true) !== false;
    }

    /**
     * Returns the singleton plugin instance
     *
     * @param \Kirby\Cms\App|null $kirby Kirby App instance
     * @return self
     */
    public static function instance(?App $kirby = null): self
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
     *
     * @return \Kirby\Cms\App
     */
    public function kirby(): App
    {
        return $this->kirby;
    }

    /**
     * Returns a log instance
     *
     * @return \distantnative\Retour\Log|\distantnative\Retour\LogDisabled
     */
    public function log()
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
     *
     * @param string $key Option key
     * @param mixed|null $default Fallback value
     * @return mixed
     */
    public function option(string $key, $default = null)
    {
        return $this->kirby()->option('distantnative.retour.' . $key, $default);
    }

    /**
     * Returns the Redirects instance
     *
     * @return \distantnative\Retour\Redirects
     */
    public function redirects(): Redirects
    {
        return $this->redirects;
    }

    /**
     * Resets the singleton plugin instance
     *
     * @return void
     */
    public static function reset()
    {
        self::$instance = null;
    }

    /**
     * Returns domain for site
     *
     * @return string|false
     */
    public function site()
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
