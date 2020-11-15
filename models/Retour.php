<?php

namespace distantnative\Retour;

use Kirby\Cms\Plugin;
use Kirby\Data\Data;
use Kirby\Database\Database;
use Kirby\Http\Header;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

class Retour
{
    /**
     * @var \distantnative\Retour\Retour
     */
    protected static $instance;

    /**
     * @var array
     */
    public $config;

    /**
     * @var \Kirby\Database\Database
     */
    public $database;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var \distantnative\Retour\Log
     */
    protected $log;

    /**
     * @var \distantnative\Retour\Routes
     */
    protected $routes;

    /**
     * @var \distantnative\Retour\Upgrades
     */
    protected $upgrades;

    /**
     * @var \Kirby\Cms\Plugin
     */
    public static $plugin;

    public function __construct()
    {
        // set config file location
        $this->file = option(
            'distantnative.retour.config',
            kirby()->root('logs') . '/config/redirects.yml'
        );

        if (is_callable($this->file) === true) {
            $this->file = call_user_func($this->file);
        }

        // load config
        try {
            $this->config = Data::read($this->file, 'yaml');
        } catch (\Throwable $e) {
            $this->config = [];
        }

        // check & run upgrades
        $this->upgrades()->run();

        // connect to database
        if (option('distantnative.retour.logs', true) === true) {
            $this->database = $this->connect();
        }
    }

    /**
     * Connects to database (and creates it if missing)
     *
     * @return \Kirby\Database\Database
     */
    protected function connect(): Database
    {
        // Get path to database file
        $file = option(
            'distantnative.retour.database',
            kirby()->root('config') . '/retour/log.sqlite'
        );

        // Support callbacks for database file option
        if (is_callable($file) === true) {
            $file = call_user_func($file);
        }

        // Make sure database is in place
        if (F::exists($file) === false) {
            $dir = dirname($file);

            if (is_dir($dir) === false) {
                Dir::make($dir);
            }

            F::copy(
                dirname(__DIR__) . '/assets/retour.sqlite',
                $file
            );
        }

        // Connect to database
        return new Database([
            'type'     => 'sqlite',
            'database' => $file
        ]);
    }

    /**
     * Returns either an existing instance or
     * creates a new instance and returns it
     *
     * @return \distantnative\Retour\Retour
     */
    public static function instance(): Retour
    {
        return static::$instance ?? static::$instance = new Retour;
    }


    /**
     * Gets or creates the Log instance
     *
     * @return \distantnative\Retour\Log
     */
    public function log(): Log
    {
        return $this->log ?? $this->log = new Log($this);
    }

    /**
     * Gets or creates the Routes instance
     *
     * @return \distantnative\Retour\Routes
     */
    public function routes(): Routes
    {
        return $this->routes ?? $this->routes = new Routes($this);
    }

    /**
     * Gets or creates the Upgrades instance
     *
     * @return \distantnative\Retour\Upgrades
     */
    public function upgrades(): Upgrades
    {
        return $this->upgrades ?? $this->upgrades = new Upgrades($this);
    }

    /**
     * Return information for Panel
     *
     * @param  bool  $reload Force reloading of update info
     * @return array
     */
    public static function info(bool $reload = false): array
    {
        return [
            'deleteAfter' => option('distantnative.retour.deleteAfter', false),
            'headers'     => Header::$codes,
            'hasLog'      => option('distantnative.retour.logs', true),
            'release'     => $release = Upgrades::latest($reload),
            'version'     => $version = static::plugin()->version(),
            'update'      => $release ? version_compare($version, $release) : null
        ];
    }

    public static function plugin(): Plugin
    {
        return static::$plugin = static::$plugin ?? kirby()->plugin('distantnative/retour');
    }

    /**
     * Updates the confg file with $data.
     * If $key is passed, only that key is updated with $data
     *
     * @param array $data
     * @param string $key
     *
     * @return bool
     */
    public function update($data, string $key = null): bool
    {
        if ($key !== null) {
            $data = array_merge($this->config, [$key => $data]);
        }

        $this->config = $data;
        return Data::write($this->file, $data, 'yaml');
    }
}
