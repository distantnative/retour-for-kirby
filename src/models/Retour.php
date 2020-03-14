<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Http\Header;
use Kirby\Toolkit\F;

class Retour
{
    /**
     * @var array
     */
    protected $config = null;

    /**
     * @var \distantnative\Retour\Logs
     */
    protected $logs = null;

    /**
     * @var \distantnative\Retour\Redirects
     */
    protected $redirects = null;

    /**
     * @var array
     */
    public static $migrations = [];

    protected static $instance = null;

    public static function instance(): Retour
    {
        if (static::$instance !== null) {
            return static::$instance;
        }

        return static::$instance = new Retour;
    }

    /**
     * Returns config array,
     * which is stored int `site/config/retour.yml`
     *
     * @return array
     */
    public function config(array $data = null): array
    {
        $file = static::root('config');

        // Write new config data
        if ($data !== null) {
            Data::write($file, $data, 'yaml');
            return $this->config = $data;
        }

        // Return cached config data
        if ($this->config !== null) {
            return $this->config;
        }

        // Load config data and return
        if (F::exists($file) === true) {
            return $this->config = Data::read($file, 'yaml');
        }

        return [];
    }

    public function logs(): Logs
    {
        return $this->logs ?? $this->logs = new Logs($this);
    }

    public function redirects(): Redirects
    {
        return $this->redirects ?? $this->redirects = new Redirects($this);
    }

    /**
     * Return information for Panel
     *
     * @return array
     */
    public static function info(): array
    {
        return [
            'headers'     => Header::$codes,
            'logs'        => option('distantnative.retour.logs'),
            'deleteAfter' => option('distantnative.retour.deleteAfter')
        ];
    }

    /**
     * Helper to get root paths to plugin locations
     *
     * @param string $type
     *
     * @return string|null
     */
    public static function root(string $type = 'root'): ?string
    {
        $root  = dirname(__DIR__, 2);
        $roots = [
            'assets'       => $root . '/assets',
            'config'       => option('distantnative.retour.config'),
            'logs'         => option('distantnative.retour.database'),
        ];

        return $roots[$type] ?? $root;
    }
}
