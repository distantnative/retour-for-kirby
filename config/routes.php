<?php

namespace distantnative\Retour;

return function ($kirby) {
    $retour = Retour::instance();
    $routes = $retour->routes()->toRules('manual', true);
    return $routes;
};
