<?php

namespace distantnative\Retour;

use DateTime;

/**
 * Panel
 * Handles (Fiber) Panel requests
 *
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */
class Panel
{
    /**
     * Returns all props for the Panel view
     *
     * @param string $tab
     * @return array
     */
    public static function props(string $tab): array
    {
        $retour   = Plugin::instance();
        $timespan = static::timespan($retour);
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
                    'icon'  => 'shuffle',
                    'badge' => count($redirects),
                    'link'  => 'retour/redirects'
                ]
            ]
        ];

        // if log feature is supported...
        if ($retour->hasLog()) {
            // purge log entries that should be auomatically deleted
            $retour->log()->purge();

            // get all data for 404 failures
            /** @var array */
            $failures = $retour->log()->fails($from, $to);

            // add additional tabs
            $props['tabs'][] = [
                'name'  => 'failures',
                'label' => t('retour.failures'),
                'icon'  => 'live',
                'badge' => count($failures),
                'link'  => 'retour/failures'
            ];
            $props['tabs'][] = [
                'name'  => 'system',
                'label' => t('retour.system'),
                'icon'  => 'info',
                'badge' => false,
                'link'  => 'retour/system'
            ];

            // get statistics data for current timeframe
            $props['stats'] = $retour->log()->stats($unit, $from, $to);
        }

        // add tab=specific data, e.g. for table rows
        switch ($tab) {
            case 'redirects':
                $props['data'] = $redirects;
                break;
            case 'failures':
                $props['data'] = $failures;
                break;
            case 'system':
                $props['data'] = [
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
                ];
                break;
        }

        return $props;
    }


    /**
     * Returns the timespan info
     * based on active session
     *
     * @return array
     * @param \distantnative\Retour\Plugin $retour
     */
    public static function timespan(Plugin $retour): array
    {
        $session = kirby()->session();
        $from    = get('from');
        $to      = get('to');

        // get timespan from query parameters
        if ($from !== null && $to !== null) {
            $data = [
                'from' => $from,
                'to'   => $to
            ];

            $session->set('retour', $data);

        // otherwise try to get from session
        } else {
            $data = $session->get('retour');
        }

        // if no data exists, use current month
        if ($data === null) {
            $data = [
                'from' => date('Y-m-01'),
                'to'   => date('Y-m-t')
            ];
        }

        // add additional data points
        $first = $retour->log()->first();
        $last = $retour->log()->last();
        $data['first'] = $first['date'] ?? null;
        $data['last']  = $last['date'] ?? null;
        $data['unit']  = static::unit($data);

        return $data;
    }

    /**
     * Returns the appropriate date unit for a
     * given timespan
     *
     * @param array $timespan
     * @return string
     */
    public static function unit(array $timespan): string
    {
        $from = new DateTime($timespan['from']);
        $to   = new DateTime($timespan['to']);

        // full units
        if ($from->format('Y-m-d') === $to->format('Y-m-d')) {
            return 'day';
        }

        if (
            $from->format('Y-m') === $to->format('Y-m') &&
            $from->format('Y-m-j') === $from->format('Y-m-1') &&
            $to->format('Y-m-j') === $to->format('Y-m-t')
        ) {
            return 'month';
        }

        if (
            $from->format('Y') === $to->format('Y') &&
            $from->format('Y-n-j') === $from->format('Y-1-1') &&
            $to->format('Y-n-j') === $to->format('Y-12-31')
        ) {
            return 'year';
        }

        // custom ranges
        if ($from->diff($to)->days > 50) {
            return 'months';
        }

        return 'days';
    }

    /**
     * Returns the Fiber view definition
     *
     * @param string $tab
     * @return array
     */
    public static function view(string $tab): array
    {
        return [
            'component' => 'k-retour-view',
            'title' => t('retour.' . $tab),
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
