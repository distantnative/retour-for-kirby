<?php

return [
    'routes' => [
        [
            'pattern' => 'retour/samples',
            'method'  => 'POST',
            'action'  => function () use ($retour) {
                require 'samples.php';
                return true;
            }
        ],
        [
            'pattern' => 'retour/load',
            'method'  => 'GET',
            'action'  => function () use ($retour) {
                $retour->load();
                return true;
            }
        ],
        [
            'pattern' => 'retour/flush',
            'method'  => 'PATCH',
            'action'  => function () use ($retour) {
                $retour->flush();
                return true;
            }
        ],
        [
            'pattern' => 'retour/redirects',
            'method'  => 'GET',
            'action'  => function () use ($retour) {
                return $retour->redirects()->data();
            }
        ],
        [
            'pattern' => 'retour/redirects',
            'method'  => 'PATCH',
            'action'  => function () use ($retour) {
                return $retour->redirects()->write($this->requestBody());
            }
        ],
        [
            'pattern' => 'retour/redirects',
            'method'  => 'POST',
            'action'  => function () use ($retour) {
                $data   = $retour->redirects()->data();
                $data[] = $this->requestBody();
                return $retour->redirects()->write($data);
            }
        ],
        [
            'pattern' => 'retour/fails/(:any)',
            'method'  => 'GET',
            'action'  => function ($sort) use ($retour) {
                return $retour->log()->fails($sort);
            }
        ],
                [
            'pattern' => 'retour/stats/(:any)/(:num?)',
            'method'  => 'GET',
            'action'  => function ($by, $offset = 0) use ($retour) {
                return $retour->stats()->get($by, $offset);
            }
        ],
        [
            'pattern' => 'retour/system',
            'method'  => 'GET',
            'action'  => function () use ($retour) {
                return $retour->system()->toArray();
            }
        ],
        [
            'pattern' => 'retour/validate',
            'method'  => 'POST',
            'action'  => function () {
                return true;
            }
        ],
    ]
];
