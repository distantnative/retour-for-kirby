<?php

namespace distantnative\Retour;

use Kirby\Form\Field;

return [
    'routes' => [
        [
            'pattern' => 'retour/routes',
            'method'  => 'GET',
            'action'  => function () {
                return Retour::instance()->routes()->toData(
                    $this->requestQuery('from'),
                    $this->requestQuery('to')
                );
            }
        ],
        [
            'pattern' => 'retour/routes',
            'method'  => 'PATCH',
            'action'  => function () {
                return Retour::instance()
                    ->routes()
                    ->update($this->requestBody());
            }
        ],
        [
            'pattern' => 'retour/failures',
            'method'  => 'GET',
            'action'  => function () {
                return Retour::instance()->log()->fails(
                    $this->requestQuery('from'),
                    $this->requestQuery('to')
                );
            }
        ],
        [
            'pattern' => 'retour/failures',
            'method'  => 'DELETE',
            'action'  => function () {
                return Retour::instance()->log()->remove(
                    $this->requestBody('path'),
                    $this->requestBody('referrer')
                );
            }
        ],
        [
            'pattern' => 'retour/stats',
            'method'  => 'GET',
            'action'  => function () {
                return Retour::instance()->log()->stats(
                    $this->requestQuery('unit'),
                    $this->requestQuery('from'),
                    $this->requestQuery('to')
                );
            }
        ],
        [
            'pattern' => 'retour/system',
            'method'  => 'GET',
            'action'  => function () {
                $reload = $this->requestQuery('reload') !== 'false';
                return Retour::info($reload);
            }
        ],
        [
            'pattern' => 'retour/log/all',
            'method'  => 'GET',
            'action'  => function () {
                return [
                    'from' => Retour::instance()->log()->first(),
                    'to'   => Retour::instance()->log()->last()
                ];
            }
        ],
        [
            'pattern' => 'retour/log/resolve',
            'method'  => 'POST',
            'action'  => function () {
                return Retour::instance()
                    ->log()
                    ->resolve($this->requestBody('path'));
            }
        ],
        [
            'pattern' => 'retour/log/flush',
            'method'  => 'POST',
            'action'  => function () {
                return Retour::instance()->log()->flush();
            }
        ],
        [
            'pattern' => 'retour/log/purge',
            'method'  => 'POST',
            'action'  => function () {
                return Retour::instance()->log()->purge();
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
