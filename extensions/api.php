<?php

namespace distantnative\Retour;

use Kirby\Form\Field;

return [
    'routes' => [
        [
            'pattern' => 'retour/redirects',
            'method'  => 'GET',
            'action'  => function (): array {
                return retour()->redirects()->toData(
                    $this->requestQuery('from'),
                    $this->requestQuery('to')
                );
            }
        ],
        [
            'pattern' => 'retour/redirects',
            'method'  => 'POST',
            'action'  => function (): bool {
                $redirect  = new Redirect($this->requestBody());
                $redirects = retour()->redirects()->prepend($redirect);
                $redirects->save();
                return true;
            }
        ],
        [
            'pattern' => 'retour/redirects/(:num)',
            'method'  => 'PATCH',
            'action'  => function (int $key): bool {
                $redirect  = new Redirect($this->requestBody());
                $redirects = retour()->redirects()->set($key, $redirect);
                $redirects->save();
                return true;
            }
        ],
        [
            'pattern' => 'retour/redirects/(:num)',
            'method'  => 'DELETE',
            'action'  => function (int $key): bool {
                $redirects = retour()->redirects()->remove($key);
                $redirects->save();
                return true;
            }
        ],
        [
            'pattern' => 'retour/failures',
            'method'  => 'GET',
            'action'  => function (): array {
                return retour()->log()->fails(
                    $this->requestQuery('from'),
                    $this->requestQuery('to')
                );
            }
        ],
        [
            'pattern' => 'retour/failures',
            'method'  => 'DELETE',
            'action'  => function (): bool {
                return retour()->log()->remove(
                    $this->requestBody('path'),
                    $this->requestBody('referrer')
                );
            }
        ],
        [
            'pattern' => 'retour/stats',
            'method'  => 'GET',
            'action'  => function (): array {
                return retour()->log()->stats(
                    $this->requestQuery('unit'),
                    $this->requestQuery('from'),
                    $this->requestQuery('to')
                );
            }
        ],
        [
            'pattern' => 'retour/meta',
            'method'  => 'GET',
            'action'  => function (): array {
                return Retour::meta();
            }
        ],
        [
            'pattern' => 'retour/log/all',
            'method'  => 'GET',
            'action'  => function (): array {
                return [
                    'from' => retour()->log()->first(),
                    'to'   => retour()->log()->last()
                ];
            }
        ],
        [
            'pattern' => 'retour/log/resolve',
            'method'  => 'POST',
            'action'  => function (): bool {
                return retour()
                    ->log()
                    ->resolve($this->requestBody('path'));
            }
        ],
        [
            'pattern' => 'retour/log/flush',
            'method'  => 'POST',
            'action'  => function (): bool {
                return retour()->log()->flush();
            }
        ],
        [
            'pattern' => 'retour/log/purge',
            'method'  => 'POST',
            'action'  => function (): bool {
                return retour()->log()->purge();
            }
        ],
        [
            'pattern' => 'retour/pagepicker',
            'method'  => 'GET',
            'action'  => function (): array {
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
