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

    protected function config(string $type = 'manual')
    {
        $config = $this->retour->config['routes'][$type];
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
    public function toData(string $begin, string $end, string $type = 'manual'): array
    {
        $routes = $this->config($type);

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
    public function toRules(string $type, bool $hasPriority = false): array
    {
        $routes = $this->config($type);

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

    public function track(string $event, array $data)
    {
        switch ($event) {
            case 'slug':
                return $this->trackSlug($data);
            case 'delete':
                return $this->trackDelete($data);
        }
    }

    protected function trackDelete(array $data)
    {
        // remove all tracked routes from this route

        // set all tracked routes leading to this route to error status code

        // insert route with from and error status code
    }

    protected function trackSlug(array $data)
    {
        // if new slug is a route.from, remove that route

        // if the old slug is a route.to, update that route as well

        // insert route with redirect status code
    }

    /**
     * Update redirect definitions in config file
     *
     * @param array $data
     */
    public function update(string $type, array $data = [])
    {
        $data = array_map(function ($route) {
            $route = new Route($route);
            return $route->toArray();
        }, $data);

        $data = array_merge($this->retour->config['routes'], [$type => $data]);
        return $this->retour->update($data, 'routes');
    }
}
