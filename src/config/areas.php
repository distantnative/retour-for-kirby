<?php

use distantnative\Retour\Panel as Retour;
use Kirby\Panel\Panel;

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
    'retour' => function ($kirby) {
        return [
            'label' => t('view.retour'),
            'icon'  => 'road-sign',
            'menu'  => true,
            'link'  => 'retour/redirects',
            'views' => [
                [
                    'pattern' => 'retour',
                    'action'  => function () {
                        Panel::go('retour/redirects');
                    }
                ],
                [
                    'pattern' => 'retour/(:any)',
                    'action'  => function (string $tab) {
                        return Retour::view($tab);
                    }
                ]
            ],
            'dialogs' => require 'area/dialogs.php'
        ];
    }
];