<?php

namespace Kirby\Retour\Panel;

use Kirby\Retour\Retour;
use Kirby\Retour\Timespan;

/**
 * Handles (Fiber) Panel view requests
 *
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */
class View
{
    /**
     * Returns all props for the Panel view
     */
    public static function props(string $tab): array
    {
        $retour   = Retour::instance();
        $timespan = Timespan::props($retour);
        ['from' => $from, 'to' => $to, 'unit' => $unit] = $timespan;

        // get all data for redirects
        // and fallback for failures
        $redirects = $retour->redirects()->toData($from, $to);
        $failures  = [];

        // props for minimal Panel view
        $props = [
            'timespan' => $timespan,
            'tab'      => $tab,
            'tabs'     => [
                [
                    'name'  => 'redirects',
                    'label' => t('retour.redirects'),
                    'badge' => count($redirects),
                    'link'  => 'retour/redirects'
                ]
            ]
        ];

        // if log feature is supported...
        if ($retour->hasLog() === true) {
            // purge log entries that should be auomatically deleted
            $retour->log()->purge();

            // get all data for 404 failures
            /** @var array */
            $failures = $retour->log()->fails($from, $to);

            // add additional tabs
            $props['tabs'][] = [
                'name'  => 'failures',
                'label' => t('retour.failures'),
                'badge' => count($failures),
                'link'  => 'retour/failures'
            ];
            $props['tabs'][] = [
                'name'  => 'system',
                'label' => t('retour.system'),
                'badge' => false,
                'link'  => 'retour/system'
            ];

            // get statistics data for current timeframe
            $props['stats'] = $retour->log()->stats($unit, $from, $to);
        }

        // add tab=specific data, e.g. for table rows
        $props['data'] = match ($tab) {
            'redirects' => $redirects,
            'failures'  => $failures,
            'system'    => [
                'redirects' => array_reduce(
                    $redirects,
                    fn ($c, $i) => $c + $i['hits'],
                    0
                ),
                'failures' => array_reduce(
                    $failures,
                    fn ($c, $i) => $c + $i['hits'],
                    0
                ),
                'deleteAfter' => $retour->option('deleteAfter', '-')
            ]
        };

        return $props;
    }

    /**
     * Returns the Fiber view definition for a tab
     */
    public static function tab(string $tab): array
    {
        return [
            'component'  => 'k-retour-' . $tab .'-view',
            'title'      => t('view.retour'),
            'breadcrumb' => [
                [
                    'label' => t('retour.' . $tab),
                    'link'  => 'retour/' . $tab,
                ]
            ],
            'props' => static::props($tab)
        ];
    }
}
