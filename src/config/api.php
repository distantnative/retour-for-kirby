<?php

namespace distantnative\Retour;

use Kirby\Form\Field;

return [
    'routes' => [
        [
            'pattern' => 'retour/redirects',
            'method'  => 'GET',
            'action'  => function () {
                return Redirects::list(
                    $this->requestQuery('from'),
                    $this->requestQuery('to')
                );
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
            'pattern' => 'retour/fails',
            'method'  => 'GET',
            'action'  => function () {
                return (new Log)->forFails(
                    $this->requestQuery('from'),
                    $this->requestQuery('to')
                );
            }
        ],
        [
            'pattern' => 'retour/stats',
            'method'  => 'GET',
            'action'  => function () {
                return Stats::get(
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
            'pattern' => 'retour/resolve',
            'method'  => 'POST',
            'action'  => function () {
                return (new Log)->resolve($this->requestBody('path'));
            }
        ],
        [
            'pattern' => 'retour/flush',
            'method'  => 'POST',
            'action'  => function () {
                (new Log)->flush();
                return true;
            }
        ],
        [
            'pattern' => 'retour/limit',
            'method'  => 'POST',
            'action'  => function () {
                $log = new Log;
                $log->limit();
                return true;
            }
        ],
        [
            'pattern' => 'retour/samples',
            'method'  => 'POST',
            'action'  => function () {
                require 'samples.php';
                return true;
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
