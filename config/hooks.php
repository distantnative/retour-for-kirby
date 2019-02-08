<?php

return [
    'route:before' => function ($route, $path) use ($retour) {
        $pattern   = $route->name() ?? $route->pattern();
        $redirects = $retour->redirects()->data();

        if (in_array($pattern, array_column($redirects, 'from')) === true) {
            $retour->tmp($path, false, $pattern);
        }
    },
    'route:after' => function ($route, $path, $method, $result) use ($retour) {
        if (empty($result) === true) {
            $retour->tmp($path, true);
        }
    }
];
