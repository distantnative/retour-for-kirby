<?php

namespace distantnative\Retour;

use peterkahl\locale\locale;

return [
    'routes' => [
        [
            'pattern' => 'retour/redirects',
            'method'  => 'GET',
            'action'  => function () {
                return Redirects::list();
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
                return (new Log)->forFails();
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
                $site  = kirby()->site();

                if (!$parent = $site->find($this->requestQuery('parent') ?? null)) {
                    $parent = $site;
                }

                $pages = $parent->children();
                $self  = [
                    'id'     => $parent->id() == '' ? null : $parent->id(),
                    'title'  => $parent->title()->value(),
                    'parent' => is_a($parent->parent(), Page::class) === true ? $parent->parent()->id() : null,
                ];

                $children = [];

                foreach ($pages as $page) {
                    if ($page->isReadable() === true) {
                        $children[] = $page->panelPickerData([
                            'image' => [],
                            'info'  => false,
                            'model' => $site,
                            'text'  => null,
                        ]);
                    }
                }

                return [
                    'model' => $self,
                    'pages' => $children
                ];
            }
        ]
    ]
];
