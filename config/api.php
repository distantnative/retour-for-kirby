<?php

return [
    'routes' => [
        [
            'pattern' => 'retour/samples',
            'method'  => 'POST',
            'action'  => function () use ($retour) {
                $retour->flush();
                Dir::copy(__DIR__ . '/samples', $retour::$dir);
                site()->update(['retour' => [
                    [
                        'status' => '301',
                        'from'   => 'blog/(:any)/(:all)',
                        'to'     => 'notes/$1/entries/$2',
                        'hits'   => null,
                        'last'   => null
                    ],
                    [
                        'status' => '503',
                        'from'   => 'pocast/this-one-episode',
                        'to'     => null,
                        'hits'   => 10,
                        'last'   => '2019-01-30 20:15'
                    ],
                    [
                        'status' => '301',
                        'from'   => 'test',
                        'to'     => 'about',
                        'hits'   => 200,
                        'last'   => '2019-02-09 09:30'
                    ],
                    [
                        'status' => 'disabled',
                        'from'   => 'soon/to/cancel',
                        'to'     => 'not/yet-ready',
                        'hits'   => null,
                        'last'   => null
                    ]
                ]]);
                return true;
            }
        ],
        [
            'pattern' => 'retour/process',
            'method'  => 'GET',
            'action'  => function () use ($retour) {
                $retour->process();
                return true;
            }
        ],
        [
            'pattern' => 'retour/flush',
            'method'  => 'PATCH',
            'action'  => function () use ($retour) {
                $retour->flush();
                $retour->redirects()->flush();
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
                $retour->redirects()->write($this->requestBody());
                return true;
            }
        ],
        [
            'pattern' => 'retour/fails/(:any)',
            'method'  => 'GET',
            'action'  => function ($sort) use ($retour) {
                return $retour->logs()->fails($sort);
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
