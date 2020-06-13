<?php

namespace distantnative\Retour;

return function ($kirby) {
    $retour = Retour::instance();
    $routes = $retour->redirects()->toRoutes(true);
    return $routes;
};
