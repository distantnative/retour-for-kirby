<?php

use Kirby\Retour\Panel\TimespanDialog;
use Kirby\Retour\Retour;

/**
 * Fiber dialogs for all Panel tabs
 *
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */

return [
    'retour.timespan' => [
        'pattern' => 'retour/timespan',
        'load'   => fn () => (new TimespanDialog())->load(),
        'submit' => fn () => (new TimespanDialog())->submit()
    ],

    'retour.redirect.delete' => [
        'pattern' => 'retour/redirects/(:any)/delete',
        'load' => fn (string $id) => [
            'component' => 'k-remove-dialog',
            'props'     => [
                'text' => t('field.structure.delete.confirm')
            ]
        ],
        'submit' => function (string $id) {
            $redirects = Retour::instance()->redirects();
            $redirects->remove(urldecode($id));
            $redirects->save();
            return true;
        }
    ],

    'retour.failure.delete' => [
        'pattern' => 'retour/failures/(:any)/delete',
        'load' => fn () => [
            'component' => 'k-remove-dialog',
            'props'     => [
                'text' => t('field.structure.delete.confirm')
            ]
        ],
        'submit' => fn (string $path) => Retour::instance()->log()->remove(urldecode($path))
    ],

    'retour.failures.flush' => [
        'pattern' => 'retour/failures/flush',
        'load' => fn () => [
            'component' => 'k-remove-dialog',
            'props'     => [
                'text' => t('retour.failures.clear.confirm')
            ]
        ],
        'submit' => fn () => Retour::instance()->log()->flush()
    ]
];
