<?php

use Kirby\Panel\Panel;
use Kirby\Retour\Panel\View;

/**
 * Sets up Panel area
 *
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */

return [
    'retour' => function () {
        return [
            'label' => t('view.retour'),
            'icon'  => 'shuffle',
            'menu'  => true,
            'link'  => 'retour/redirects',
            'views' => [
                [
                    'pattern' => 'retour',
                    'action'  => fn () => Panel::go('retour/redirects')
                ],
                [
                    'pattern' => 'retour/(:any)',
                    'action'  => fn (string $tab) => View::tab($tab)
                ]
            ],
            'dialogs' => require 'dialogs.php',
            'drawers' => require 'drawers.php'
        ];
    }
];
