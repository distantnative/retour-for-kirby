<?php

namespace distantnative\Retour;

use Closure;

use Kirby\Toolkit\Collection;

class Redirects extends Collection
{
    /**
     * Takes a config array and turns it into
     * a collection of redirect objects
     *
     * @param array $data
     * @return static
     */
    public static function factory(array $config): Redirects
    {
        $redirects = array_map(function ($data) {
            return new Redirect($data);
        }, $config);

        return new static($redirects);
    }

    public function save(): void
    {
        $data = $this->toArray();
        Config::set('redirects', $data);
    }

    /**
     * Turns collection into array, by default turning
     * Redirect objects into array as well
     *
     * @param Closure $map
     * @return array
     */
    public function toArray(Closure $map = null): array
    {
        $array = parent::toArray($map ?? function ($redirect) {
            return $redirect->toArray();
        });

        return array_values($array);
    }

    /**
     * Return redirects data combined with log data
     *
     * @param string $from
     * @param string $to
     * @return array
     */
    public function toData(string $from, string $to): array
    {
        // If logging is disabled, return without data
        if (Retour::meta()['hasLog'] === false) {
            return $this->toArray();
        }

        return $this->toArray(function ($redirect) use ($from, $to) {
            $data = $redirect->toArray();
            $log  = retour()->log()->redirect($data['from'], $from, $to);
            return array_merge($data, $log);
        });
    }

    /**
     * Get routes config for all redirects
     *
     * @return array
     */
    public function toRoutes(bool $hasPriority = false): array
    {
        // Filter: no routes for disabled redirects
        //         and match the priority parameter
        $redirects = $this->filter(function ($route) use ($hasPriority) {
            return $route->isActive() && $route->priority() === $hasPriority;
        });

        // create route array for each redirect
        return $redirects->toArray(function ($route) {
            return $route->toRoute();
        });
    }
}
