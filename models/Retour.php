<?php

namespace distantnative\Retour;

use Kirby\Cms\Plugin;
use Kirby\Data\Data;
use Kirby\Database\Database;
use Kirby\Http\Header;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

use Closure;

class Retour
{
    /**
     * @var \distantnative\Retour\Retour
     */
    protected static $instance;

    /**
     * @var \distantnative\Retour\Config
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
     * @var \Kirby\Cms\Plugin
     */
    public static $plugin;

    public function __construct()
    {
        // load config
        $config = kirby()->root('config') . '/redirects.yml';
        $config = option('distantnative.retour.config', $config);
        $this->config = new Config($config);

        static::$instance = $this;

        // check & run upgrades
        $this->upgrade();

        // connect to database
        if (option('distantnative.retour.logs', true) === true) {
            $this->database = $this->database();
        }
    }

    /**
     * Returns Config instance
     *
     * @return \distantnative\Retour\Config
     */
    public function config(): Config
    {
        return $this->config;
    }

    /**
     * Connects to database (and creates it if missing)
     *
     * @return \Kirby\Database\Database
     */
    protected function database(): Database
    {
        // Get path to database file
        $file = kirby()->root('logs') . '/retour/log.sqlite';
        $file = option('distantnative.retour.database', $file);

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

            F::copy(dirname(__DIR__) . '/assets/retour.sqlite', $file);
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
        return static::$instance = static::$instance ?? new static();
    }


    /**
     * Gets or creates the Log instance
     *
     * @return \distantnative\Retour\Log
     */
    public function log(): Log
    {
        return $this->log ?? $this->log = new Log($this->database);
    }

    /**
     * Gets or creates the Routes instance
     *
     * @return \distantnative\Retour\Routes
     */
    public function routes(): Routes
    {
        $routes = $this->config()->data('routes', []);
        return $this->routes ?? $this->routes = Routes::factory($routes);
    }

    public function upgrade(): void
    {
        $current    = $this->config()->data('schema', '2.3.1');
        $migrations = array_map(function (Closure $closure) {
            return $closure->bindTo($this);
        }, require dirname(__DIR__) . '/config/migrations.php');
        $migrator   = new Migrator($current, $migrations);

        if ($latest = $migrator->run()) {
            $this->config()->set('schema', $latest);
        }
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
            'hasLog'      => option('distantnative.retour.logs', true)
        ];
    }

    public static function plugin(): Plugin
    {
        return static::$plugin = static::$plugin ?? kirby()->plugin('distantnative/retour');
    }
}
