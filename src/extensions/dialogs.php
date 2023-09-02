<?php

use distantnative\Retour\Plugin as Retour;

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
    'retour.redirect.delete' => [
        'pattern' => 'retour/redirects/(:any)/delete',
        'load' => fn (string $id) => [
            'component' => 'k-remove-dialog',
            'props'     => ['text' => t('field.structure.delete.confirm')]
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
            'props'     => ['text' => t('field.structure.delete.confirm')]
        ],
        'submit' => function (string $path) {
            return Retour::instance()->log()->remove(urldecode($path));
        }
    ],

    'retour.failures.flush' => [
        'pattern' => 'retour/failures/flush',
        'load' => fn () => [
            'component' => 'k-remove-dialog',
            'props'     => ['text' => t('retour.failures.clear.confirm') ]
        ],
        'submit' => function () {
            return Retour::instance()->log()->flush();
        }
    ]
];
