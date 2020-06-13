<?php

namespace distantnative\Retour;

use Kirby\Data\Data;

class Routes
{

    /**
     * Location of the config file
     *
     * @var string
     */
    protected $file;

    public function __construct()
    {
        $this->file = option('distantnative.retour.config');

        if (is_callable($this->file) === true) {
            $this->file = call_user_func($this->file);
        }
    }

    /**
     * Load all definitions from config file and
     * turn them into Route objects
     *
     * @return array
     */
    protected function load()
    {
        try {
            $data = Data::read($this->file, 'yaml');
            return array_map(function ($route) {
                return new Route($route);
            }, $data);

        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Return redirects definitions combined with log data
     *
     * @param string $begin
     * @param string $end
     *
     * @return array
     */
    public function toData(string $begin, string $end): array
    {
        $routes = $this->load();

        // turn Route objects into arrays
        $data = array_map(function ($route) {
            return $route->toArray();
        }, $routes);

        // if logging is disabled, return without data
        if (option('distantnative.retour.logs') !== false) {
           return $data;
        }

        return array_map(function ($route) use ($begin, $end) {
            return Retour::instance()->log()->redirect(
                $route,
                $begin,
                $end
            );
        }, $data);
    }

    /**
     * Get routes config for all redirects
     *
     * @return array
     */
    public function toRules(bool $hasPriority = false): array
    {
        $data = $this->load();

        // Filter: no routes for disabled redirects
        //         and match the priority parameter
        $data = array_filter($data, function ($route) use ($hasPriority) {
            return $route->isActive() && $route->hasPriority() === $hasPriority;
        });

        // create route array for each redirect
        $data = array_map(function ($route) {
            return $route->toRule();
        }, $data);

        return $data;
    }

    /**
     * Update redirect definitions in config file
     *
     * @param array $data
     */
    public function update(array $data = [])
    {
        $data = array_map(function ($route) {
            $route = new Route($route);
            return $route->toArray();
        }, $data);
        return Data::write($this->file, $data, 'yaml');
    }
}
