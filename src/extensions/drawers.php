<?php

use distantnative\Retour\Plugin as Retour;
use Kirby\Http\Header;
use Kirby\Panel\Panel;
use Kirby\Toolkit\I18n;

/**
 * Fiber drawers for all Panel tabs
 *
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */


/**
 * Shared fields definition for several drawers
 */
$fields = function (Retour $retour): array {
    return [
        'from' => [
            'label'    => t('retour.redirects.from'),
            'type'     => 'text',
            'before'   => $retour->site(),
            'counter'  => false,
            'required' => true,
            'help'     => I18n::template('retour.redirects.from.help', [
                'docs' => 'https://github.com/distantnative/retour-for-kirby#path'
            ])
        ],
        'to' => [
            'label'    => t('retour.redirects.to'),
            'type'     => 'text',
            'counter'  => false,
            'help'     => t('retour.redirects.to.help')
        ],
        'status' => [
            'label'    => t('retour.redirects.status'),
            'type'     => 'rt-status',
            'options'  => array_map(fn ($code) => [
                'text'  => ltrim($code, '_') . ' - ' . Header::$codes[$code],
                'value' => ltrim($code, '_')
            ], array_keys(Header::$codes)),
            'width'    => '1/2',
            'help'     => I18n::template('retour.redirects.status.help', [
                'docs' => 'https://github.com/distantnative/retour-for-kirby#status'
            ])
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
};

return [
    'retour.redirect.create' => [
        'pattern' => 'retour/redirects/create',
        'load' => fn () => [
            'component' => 'k-form-drawer',
            'props' => [
                'fields' => $fields(Retour::instance()),
                'icon'   => 'add',
                'title'  => I18n::translate('add'),
            ]
        ],
        'submit' => function () {
            $redirects = Retour::instance()->redirects();
            $redirects->create();
            $redirects->save();
            return true;
        }
    ],

    'retour.redirect.edit' => [
        'pattern' => 'retour/redirects/(:any)/edit',
        'load' => function (string $id) use ($fields) {
            // get redirect
            $retour    = Retour::instance();
            $redirects = $retour->redirects();
            $redirect  = $redirects->get(urldecode($id));

            $fields = $fields($retour);

            // set autofocus if specific column cell
            // was passed
            if (($field = get('column')) && isset($fields[$field]) === true) {
                $fields[$field]['autofocus'] = true;
            }

            return [
                'component' => 'k-form-drawer',
                'props' => [
                    'fields' => $fields,
                    'icon'   => 'shuffle',
                    'title'  => $redirect->from(),
                    'value'  => $redirect->toArray(),
                ]
            ];
        },
        'submit' => function (string $id) {
            $redirects = Retour::instance()->redirects();
            $redirects->update(urldecode($id));
            $redirects->save();
            return true;
        }
    ],

    'retour.failure.resolve' => [
        'pattern' => 'retour/failures/(:any)/resolve',
        'load' => fn (string $path) => [
            'component' => 'k-form-drawer',
            'props' => [
                'fields' => $fields(Retour::instance()),
                'value' => [
                    'from' => str_replace("\x1D",'/', urldecode($path))
                ]
            ]
        ],
        'submit' => function (string $path) {
            $plugin    = Retour::instance();
            $redirects = $plugin->redirects();
            $redirects->create();
            $redirects->save();
            $log = $plugin->log();
            $log->resolve(urldecode($path));

            Panel::go('retour/redirects');
        }
    ]
];
