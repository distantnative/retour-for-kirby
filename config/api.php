<?php

namespace distantnative\Retour;

use Kirby\Form\Field;

return [
    'routes' => [
        [
            'pattern' => 'retour/redirects',
            'method'  => 'GET',
            'action'  => function () {
                return Retour::instance()->redirects()->get(
                    $this->requestQuery('from'),
                    $this->requestQuery('to')
                );
            }
        ],
        [
            'pattern' => 'retour/redirects',
            'method'  => 'PATCH',
            'action'  => function () {
                return Retour::instance()
                        ->redirects()
                        ->update($this->requestBody());
            }
        ],
        [
            'pattern' => 'retour/fails',
            'method'  => 'GET',
            'action'  => function () {
                return Retour::instance()->logs()->fails(
                    $this->requestQuery('from'),
                    $this->requestQuery('to')
                );
            }
        ],
        [
            'pattern' => 'retour/stats',
            'method'  => 'GET',
            'action'  => function () {
                return Retour::instance()->logs()->stats(
                    $this->requestQuery('view'),
                    $this->requestQuery('from'),
                    $this->requestQuery('to')
                );
            }
        ],
        [
            'pattern' => 'retour/system',
            'method'  => 'GET',
            'action'  => function () {
                return Retour::info();
            }
        ],
        [
            'pattern' => 'retour/logs/all',
            'method'  => 'GET',
            'action'  => function () {
                return [
                    'first' => Retour::instance()->logs()->first(),
                    'last'  => Retour::instance()->logs()->last()
                ];
            }
        ],
        [
            'pattern' => 'retour/logs/resolve',
            'method'  => 'POST',
            'action'  => function () {
                return Retour::instance()
                        ->logs()
                        ->resolve($this->requestBody('path'));
            }
        ],
        [
            'pattern' => 'retour/logs/flush',
            'method'  => 'POST',
            'action'  => function () {
                return Retour::instance()->logs()->flush();
            }
        ],
        [
            'pattern' => 'retour/logs/purge',
            'method'  => 'POST',
            'action'  => function () {
                return Retour::instance()->logs()->purge();
            }
        ],
        [
            'pattern' => 'retour/pagepicker',
            'method'  => 'GET',
            'action'  => function () {
                $field = new Field('pages', [
                    'model' => kirby()->site()
                ]);

                return $field->pagepicker([
                    'image'    => $field->image(),
                    'info'     => $field->info(),
                    'limit'    => $field->limit(),
                    'page'     => $this->requestQuery('page'),
                    'parent'   => $this->requestQuery('parent'),
                    'query'    => $field->query(),
                    'search'   => $this->requestQuery('search'),
                    'subpages' => $field->subpages(),
                    'text'     => $field->text()
                ]);
            }
        ]
    ]
];
