<?php

namespace Kirby\Retour\Panel;

use DateInterval;
use DateTime;
use Kirby\Cms\App;
use Kirby\Panel\Panel as Kirby;
use Kirby\Retour\Panel;
use Kirby\Retour\Retour;
use Kirby\Toolkit\Date;
use Kirby\Toolkit\I18n;

/**
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */
class TimespanDialog
{
    protected function date(
        DateTime|string|null $date,
        string $format = 'Y-m-d H:i:s'
    ): string|null {
        if (is_string($date) === true) {
            $date = Date::optional($date);
        }

        return $date?->format($format);
    }

    public function load(): array
    {
        $retour = Retour::instance();
        $data   = Panel::timespan($retour);

        $min  = $this->date($data['first']);
        $day  = DateInterval::createFromDateString('1 day');
        $max  = $this->date(Date::now()->add($day));

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
                    'from' => $this->date($data['from']),
                    'to'   => $this->date($data['to'])
                ],
                'submitButton' => [
                    'text' => I18n::translate('change')
                ]
            ]
        ];
    }

    public function submit(): array
    {
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
            'redirect' => Kirby::referrer()
        ];
    }
}
