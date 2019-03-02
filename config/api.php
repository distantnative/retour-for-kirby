<?php

namespace distantnative\Retour;

return [
    'routes' => [
        [
            'pattern' => 'retour/process',
            'method'  => 'POST',
            'action'  => function () {
                return Retour::process();
            }
        ],
        [
            'pattern' => 'retour/flush',
            'method'  => 'POST',
            'action'  => function () {
                return Retour::flush();
            }
        ],
        [
            'pattern' => 'retour/redirects',
            'method'  => 'GET',
            'action'  => function () {
                return Redirects::read();
            }
        ],
        [
            'pattern' => 'retour/redirects',
            'method'  => 'PATCH',
            'action'  => function () {
                return Redirects::write($this->requestBody());
            }
        ],
        [
            'pattern' => 'retour/logs',
            'method'  => 'GET',
            'action'  => function () {
                return array_values(Logs::read());
            }
        ],
        [
            'pattern' => 'retour/stats/(:any)/(:num?)',
            'method'  => 'GET',
            'action'  => function ($by, $offset = 0) {
                return Stats::get($by, $offset);
            }
        ],
        [
            'pattern' => 'retour/system',
            'method'  => 'GET',
            'action'  => function () {
                return System::toArray();
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
