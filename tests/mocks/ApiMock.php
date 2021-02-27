<?php

namespace distantnative\Retour;

class RetourApiMock {

    protected $routes;

    public function __construct(array $routes)
    {
        $this->routes = Routes::factory($routes);
    }

    public function routes()
    {
        return $this->routes;
    }
}
