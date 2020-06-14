<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Database\Database;
use Kirby\Http\Header;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;

class Retour
{
    protected static $instance;

    protected $file;
    public $config;
    public $database;

    protected $log;
    protected $routes;
    protected $upgrades;

    public static $plugin;

    public function __construct()
    {
        // set config file location
        $this->file = option('distantnative.retour.config');

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
        if (option('distantnative.retour.logs') === true) {
            $this->database = $this->connect();
        }
    }

    /**
     * Connects to database (and creates it if missing)
     *
     * @return void
     */
    protected function connect()
    {
        // Get path to database file
        $file = option('distantnative.retour.database');

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


    public function log()
    {
        return $this->log ?? $this->log = new Log($this);
    }

    public function routes()
    {
        return $this->routes ?? $this->routes = new Routes($this);
    }

    public function upgrades()
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
            'deleteAfter' => option('distantnative.retour.deleteAfter'),
            'headers'     => Header::$codes,
            'hasLog'      => option('distantnative.retour.logs'),
            'hasTracking' => option('distantnative.retour.tracking') ||
                             option('distantnative.retour.deletions'),
            'release'     => $release = Upgrades::latest($reload),
            'version'     => $version = static::plugin()->version(),
            'update'      => $release ? version_compare($version, $release) : null
        ];
    }

    public static function plugin()
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
     * @return void
     */
    public function update($data, string $key = null)
    {
        if ($key !== null) {
            $data = array_merge($this->config, [$key => $data]);
        }

        $this->config = $data;
        return Data::write($this->file, $data, 'yaml');
    }
}
