<?php

use distantnative\Retour\Plugin as Retour;
use distantnative\Retour\Redirect;
use Kirby\Http\Header;
use Kirby\Panel\Panel;
use Kirby\Toolkit\I18n;

$fields = [
    'from' => [
        'label'    => t('retour.redirects.from'),
        'type'     => 'text',
        'before'   => preg_replace('$^(http(s)?\:\/\/(www\.)?)$', '', kirby()->url()) . '/',
        'counter'  => false,
        'required' => true,
        'help'     => I18n::template('retour.redirects.from.help', ['docs' => 'https://github.com/distantnative/retour-for-kirby'])
        ],
    'to' => [
        'label'    => t('retour.redirects.to'),
        'type'     => 'rt-destination',
        'counter'  => false,
        'help'     => t('retour.redirects.to.help')
    ],
    'status' => [
        'label'    => t('retour.redirects.status'),
        'type'     => 'rt-status',
        'options'  => array_map(function ($code) {
            return [
                'text'  => ltrim($code, '_') . ' - ' . Header::$codes[$code],
                'value' => ltrim($code, '_')
            ];
        }, array_keys(Header::$codes)),
        'width'    => '1/2',
        'help'     => I18n::template('retour.redirects.status.help', ['docs' => 'https://github.com/distantnative/retour-for-kirby'])
    ],
    'priority' => [
        'label'    => t('retour.redirects.priority'),
        'type'     => 'toggle',
        'icon'     => 'bolt',
        'width'    => '1/2',
        'help'     => t('retour.redirects.priority.help')
    ],
    'comment' => [
        'label'    => t('retour.redirects.comment'),
        'type'     => 'textarea',
        'buttons'  => false,
        'help'     => t('retour.redirects.comment.help')
    ]
];

function createRedirect(int $key = null) {
    $redirect  = new Redirect([
        'from'      => get('from'),
        'to'        => get('to'),
        'status'    => get('status'),
        'priority'  => get('priority'),
        'comment'   => get('comment')
    ]);
    $redirects = Retour::instance()->redirects();

    if ($key !== null)  {
        $redirects = $redirects->set((int)$id, $redirect);
    } else {
        $redirects = $redirects->prepend($redirect);
    }

    $redirects->save();
};

return [
    'retour.redirect.create' => [
        'pattern' => 'retour/redirects/create',
        'load' => function () use ($fields) {
            return [
              'component' => 'k-form-dialog',
              'props' => [
                    'fields' => $fields,
                    'size'  => 'huge'
                ]
            ];
        },
        'submit' => function () {
            createRedirect();
            return true;
        }
    ],

    'retour.redirect.edit' => [
        'pattern' => 'retour/redirects/(:any)/edit',
        'load' => function (string $id) use ($fields) {
            $redirects = Retour::instance()->redirects();
            $redirect  = $redirects->nth((int)$id);

            return [
              'component' => 'k-form-dialog',
              'props' => [
                    'fields' => $fields,
                    'value' => $redirect->toArray(),
                    'size'  => 'huge'
                ]
            ];
        },
        'submit' => function (string $id) {
            createRedirect((int)$id);
            return true;
        }
    ],

    'retour.redirect.delete' => [
        'pattern' => 'retour/redirects/(:any)/delete',
        'load' => function (string $id) {
            return [
              'component' => 'k-remove-dialog',
              'props' => [
                    'text' => t('field.structure.delete.confirm')
                ]
            ];
        },
        'submit' => function (string $id) {
            $redirects = Retour::instance()->redirects()->remove((int)$id);
            $redirects->save();
            return true;
        }
    ],

    'retour.failure.resolve' => [
        'pattern' => 'retour/failures/(:any)/resolve',
        'load' => function (string $path) use ($fields) {
            return [
              'component' => 'k-form-dialog',
              'props' => [
                'fields' => $fields,
                'value' => [
                    'from' => $path
                ],
                'size'  => 'huge'
              ]
            ];
        },
        'submit' => function (string $path) {
            createRedirect();
            Retour::instance()->log()->resolve($path);
            Panel::go('retour/redirects');
        }
    ],

    'retour.failure.delete' => [
        'pattern' => 'retour/failures/(:any)/delete',
        'load' => function () {
            return [
              'component' => 'k-remove-dialog',
              'props' => [
                  'text' => t('field.structure.delete.confirm')
                ]
            ];
        },
        'submit' => function (string $path) {
            return Retour::instance()->log()->remove($path);
        }
    ],

    'retour.failures.flush' => [
        'pattern' => 'retour/failures/flush',
        'load' => function () {
            return [
                'component' => 'k-remove-dialog',
                'props' => [
                    'text' => t('retour.failures.clear.confirm')
                ]
            ];
        },
        'submit' => function () {
            return Retour::instance()->log()->flush();
        }
    ]
];
