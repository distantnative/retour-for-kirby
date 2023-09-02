<?php

use distantnative\Retour\Plugin as Retour;
use distantnative\Retour\Panel as RetourPanel;
use Kirby\Cms\App;
use Kirby\Panel\Panel;
use Kirby\Toolkit\Date;
use Kirby\Toolkit\I18n;

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
        'load' => function () {
            $retour = Retour::instance();
            $data   = RetourPanel::timespan($retour);

            $from = Date::optional($data['from'])->format('Y-m-d H:i:s');
            $to   = Date::optional($data['to'])->format('Y-m-d H:i:s');
            $min  = Date::optional($data['first'])->format('Y-m-d H:i:s');
            $max  = Date::now()->add(DateInterval::createFromDateString('1 day'))->format('Y-m-d H:i:s');

            return [
                'component' => 'k-form-dialog',
                'props'     => [
                    'fields' => [
                        'from' => [
                            'type'     => 'date',
                            'label'    => t('retour.timespan.from.label'),
                            'required' => true,
                            'time'     => false,
                            'min'      => $min,
                            'max'      => $max
                        ],
                        'to' => [
                            'type'     => 'date',
                            'label'    => t('retour.timespan.to.label'),
                            'required' => true,
                            'time'     => false,
                            'min'      => $min,
                            'max'      => $max
                        ]
                    ],
                    'value' => [
                        'from' => $from,
                        'to'   => $to
                    ],
                    'submitButton' => [
                        'text' => I18n::translate('change')
                    ]
                ]
            ];
        },
        'submit' => function () {
            $kirby   = App::instance();
            $data    = $kirby->request()->get(['from', 'to']);
            $from    = Date::optional($data['from']);
            $to      = Date::optional($data['to']);

            if ($to->isBefore($from) === true) {
                $to = $from;
            }

            $session = $kirby->session();
            $session->set('retour', [
                'from' => $from->format('Y-m-d'),
                'to'   => $to->format('Y-m-d')
            ]);

            return [
				'redirect' => Panel::referrer()
			];
        }
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
