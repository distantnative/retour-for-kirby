<?php

namespace distantnative\Retour;

use Kirby\Form\Field;

return [
    'routes' => [
        [
            'pattern' => 'retour/meta',
            'method'  => 'GET',
            'action'  => function (): array {
                // Purge log entries that should be auomatically deleted
                retour()->log()->purge();

                // Get meta information
                $meta = Retour::meta();

                // Add log informaiton if activated
                if ($meta['hasLog'] !== false) {
                    $meta = array_merge($meta, [
                        'first' => retour()->log()->first()['date'],
                        'last'  => retour()->log()->last()['date']
                    ]);
                }

                return $meta;
            }
        ],
        [
            'pattern' => 'retour/data',
            'method'  => 'GET',
            'action'  => function (): array {
                $retour = retour();
                $data = [
                    'redirects' => retour()->redirects()->toData(
                        $this->requestQuery('from'),
                        $this->requestQuery('to')
                    )
                ];

                if (Retour::meta()['hasLog'] !== false) {
                    $data['fails'] = $retour->log()->fails(
                        $this->requestQuery('from'),
                        $this->requestQuery('to')
                    );

                    $data['stats'] = $retour->log()->stats(
                        $this->requestQuery('unit'),
                        $this->requestQuery('from'),
                        $this->requestQuery('to')
                    );
                }

                return $data;
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
            'pattern' => 'retour/fails',
            'method'  => 'DELETE',
            'action'  => function (): bool {
                return retour()->log()->remove(
                    $this->requestBody('path'),
                    $this->requestBody('referrer')
                );
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
