<?php

namespace distantnative\Retour;

use Kirby\Toolkit\Collection;
use Closure;

class Routes extends Collection
{

    /**
     * Takes an array of routes data and turns it into
     * a collection of route objects
     *
     * @param array $data
     * @return \distantnative\Retour\Routes
     */
    public static function factory(array $data): Routes
    {
        $routes = array_map(function ($route) {
            return new Route($route);
        }, $data);

        return new static($routes);
    }

    /**
     * Stores the routes in Retour config file
     *
     * @return bool
     */
    public function save(): bool
    {
        return retour()->config()->set('routes', $this->toArray());
    }

    /**
     * Turns collection into array, by default turning
     * Route object into array as well
     *
     * @param Closure $map
     * @return array
     */
    public function toArray(Closure $map = null): array
    {
        $array = parent::toArray($map ?? function ($route) {
            return $route->toArray();
        });

        return array_values($array);
    }

    /**
     * Return redirects definitions combined with log data
     *
     * @param string $from
     * @param string $to
     *
     * @return array
     */
    public function toData(string $from, string $to): array
    {
        // if logging is disabled, return without data
        if (option('distantnative.retour.logs', true) !== true) {
            return $this->toArray();
        }

        return $this->toArray(function ($route) use ($from, $to) {
            return retour()->log()->redirect(
                $route->toArray(),
                $from,
                $to
            );
        });
    }

    /**
     * Get routes config for all redirects
     *
     * @return array
     */
    public function toRules(bool $hasPriority = false): array
    {
        // Filter: no routes for disabled redirects
        //         and match the priority parameter
        $routes = $this->filter(function ($route) use ($hasPriority) {
            return $route->isActive() && $route->hasPriority() === $hasPriority;
        });

        // create route array for each redirect
        return $routes->toArray(function ($route) {
            return $route->toRule();
        });
    }

}
