<?php

return [
    'route:before' => function ($route, $path) use ($retour) {
        $retour->redirects()->hit($route->pattern(), $path);
    },
    'route:after' => function ($route, $path, $method, $result) use ($retour) {
        if (empty($result) === true) {
            $retour->log()->add($path);
        }
    }
];
