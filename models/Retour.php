<?php

namespace distantnative\Retour;

use Kirby\Http\Header;

final class Retour
{
    /**
     * @var \distantnative\Retour\Retour
     */
    public static $instance;

    /**
     * @var \distantnative\Retour\Log|\distantnative\Retour\LogDisabled|null
     */
    protected $log;

    /**
     * @var \distantnative\Retour\Redirects
     */
    protected $redirects;

    public function __construct()
    {
        // Load and set config
        $default = kirby()->root('config') . '/retour.yml';
        $path    = option('distantnative.retour.config', $default);
        Config::load($path);

        // Initialize redirects
        $redirects = Config::get('redirects') ?? [];
        $this->redirects = Redirects::factory($redirects);

        static::$instance = $this;
    }

    /**
     * Returns either an existing instance or
     * creates a new instance and returns it
     *
     * @return self
     */
    public static function instance(): Retour
    {
        /**
         * @psalm-suppress RedundantPropertyInitializationCheck
         */
        return static::$instance ?? new self();
    }

    /**
     * Gets or creates the Log instance
     *
     * @return \distantnative\Retour\Log|\distantnative\Retour\LogDisabled
     */
    public function log()
    {
        if ($this->log !== null) {
            return $this->log;
        }

        if (static::meta()['hasLog'] === false) {
            return $this->log = new LogDisabled();
        }

        return $this->log = new Log();
    }

    /**
     * Return information for Panel
     *
     * @return array
     */
    public static function meta(): array
    {
        return [
            'deleteAfter' => option('distantnative.retour.deleteAfter', false),
            'headers'     => Header::$codes,
            'hasLog'      => option('distantnative.retour.logs', true)
        ];
    }

    /**
     * Gets or creates the Redirects instance
     *
     * @return \distantnative\Retour\Redirects
     */
    public function redirects(): Redirects
    {
        return $this->redirects;
    }
}
