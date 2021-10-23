<?php

use distantnative\Retour\Plugin as Retour;
use Kirby\Http\Header;
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
            $redirect  = new Redirect($this->requestBody());
            $redirects = $retour->redirects()->prepend($redirect);
            $redirects->save();
            return true;
        }
    ],
    'retour.redirect.edit' => [
        'pattern' => 'retour/redirects/(:any)/edit',
        'load' => function (string $id) use ($fields) {
            return [
              'component' => 'k-form-dialog',
              'props' => [
                    'fields' => $fields,
                    'value' => [],
                    'size'  => 'huge'
                ]
            ];
        },
        'submit' => function (string $id) {
            $redirect  = new Redirect($this->requestBody());
            $redirects = $retour->redirects()->set($key, $redirect);
            $redirects->save();
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
            $redirects = Retour::instance()->redirects()->remove($id);
            $redirects->save();
            return true;
        }
    ],
    'retour.failure.delete' => [
        'pattern' => 'retour/failure/(:any)/delete',
        'load' => function () {
            return [
              'component' => 'k-remove-dialog',
              'props' => [
                  'text' => t('field.structure.delete.confirm')
                ]
            ];
        },
        'submit' => function () {
            return Retour::instance()->log()->remove(
                get('path'),
                get('referrer')
            );
        }
    ],
    'retour.failure.resolve' => [
        'pattern' => 'retour/failure/(:any)/resolve',
        'load' => function () {
            return [
              'component' => 'k-form-dialog',
              'props' => [
                ]
            ];
        },
        'submit' => function () {
            return $retour->log()->resolve($this->requestBody('path'));
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
