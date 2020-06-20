<?php

namespace distantnative\Retour;


class Routes
{

    protected $retour;

    /**
     * @param \distantnative\Retour\Retour $retour
     */
    public function __construct(Retour $retour)
    {
        $this->retour = $retour;
    }

    protected function config()
    {
        $config = $this->retour->config['routes'];
        return array_map(function ($config) {
            return new Route($config);
        }, $config);
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
        $routes = $this->config();

        // turn Route objects into arrays
        $data = array_map(function ($route) {
            return $route->toArray();
        }, $routes);

        // if logging is disabled, return without data
        if (option('distantnative.retour.logs') !== true) {
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
        $routes = $this->config();

        // Filter: no routes for disabled redirects
        //         and match the priority parameter
        $routes = array_filter($routes, function ($route) use ($hasPriority) {
            return $route->isActive() && $route->hasPriority() === $hasPriority;
        });

        // create route array for each redirect
        $data = array_map(function ($route) {
            return $route->toRule();
        }, $routes);

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

        return $this->retour->update($data, 'routes');
    }
}
