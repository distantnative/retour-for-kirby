<?php

namespace Kirby\Retour;

use Kirby\Cms\App;
use Kirby\Toolkit\Date;

/**
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */
class Timespan
{

    /**
     * Returns the timespan info based on active session
     */
    public static function get(): array
    {
        $retour  = Retour::instance();
        $session = App::instance()->session();
        $data    = static::query();

        if ($data['from'] !== null && $data['to']  !== null) {
            // get timespan from query parameters
            $session->set('retour', $data);

        } else {
            // otherwise try to get from session
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
        $last  = $retour->log()->last();
        $data['first'] = $first['date'] ?? null;
        $data['last']  = $last['date'] ?? null;
        $data['unit']  = static::unit($data);

        return $data;
    }

    public static function query(): array
    {
        return App::instance()->request()->get(['from', 'to']);
    }

    /**
     * Returns the appropriate date unit for a given timespan
     */
    public static function unit(array $timespan): string
    {
        $from = Date::optional($timespan['from']);
        $to   = Date::optional($timespan['to']);

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
}
