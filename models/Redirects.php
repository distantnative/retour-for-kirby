<?php

namespace distantnative\Retour;

use Closure;

use Kirby\Toolkit\Collection;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class Redirects extends Collection
{
    /**
     * Takes a config array and turns it into
     * a collection of redirect objects
     *
     * @param array $config
     * @return self
     */
    public static function factory(array $config): self
    {
        $redirects = array_map(function ($data) {
            return new Redirect($data);
        }, $config);

        return new self($redirects);
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
        $array = parent::toArray($map ?? function (Redirect $redirect): array {
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

        return $this->toArray(function (Redirect $redirect) use ($from, $to): array {
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
        $redirects = $this->filter(function (Redirect $redirect) use ($hasPriority): bool {
            return $redirect->isActive() && $redirect->priority() === $hasPriority;
        });

        // create route array for each redirect
        return $redirects->toArray(function (Redirect $route) {
            return $route->toRoute();
        });
    }
}
