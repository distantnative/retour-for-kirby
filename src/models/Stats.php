<?php

namespace distantnative\Retour;

class Stats
{

    /**
     * Get stats data for specified timeframe
     *
     * @param string $by
     * @param int $offset
     * @return array
     */
    public static function get(string $by, int $offset): array
    {
        $log = new Log;

        switch ($by) {
            case 'year':
                $time = strtotime('today ' . $offset . ' year');
                $start = date('Y-01-01 00:00:00', $time);
                $end = date('Y-12-31 23:59:59', $time);
                $data = $log->forStats($start, $end, '%W');
                $data = self::fill($data, $start, $end, 'week', '%W');
                break;

            case 'month':
                $start = date('Y-m-01 00:00:00 ', time());
                $start = strtotime($start. $offset . ' month');
                $start = date('Y-m-01 00:00:00', $start);
                $end = date('Y-m-t 23:59:59', strtotime($start));
                $data = $log->forStats($start, $end, '%d');
                $data = self::fill($data, $start, $end, 'day', '%d');
                break;

            case 'week':
                $start = date('w') === 1 ? strtotime('today') : strtotime('last Monday ' . $offset . ' week');
                $start = date('Y-m-d 00:00:00', $start);
                $end = strtotime($start . ' +6 day');
                $end = date('Y-m-d 23:59:59', $end);
                $data = $log->forStats($start, $end, '%d');
                $data = self::fill($data, $start, $end, 'day', '%d');
                break;

            case 'day':
                $time = strtotime('today ' . $offset . ' day');
                $start = date('Y-m-d 00:00:00', $time);
                $end = date('Y-m-d 23:59:59', $time);
                $data = $log->forStats($start, $end, '%H');
                $data = self::fill($data, $start, $end, 'hour', '%H');
                break;
        }

        return [
            'data' => $data,
            'title' => self::title($start, $end)
        ];
    }

    /**
     * Fill gaps in timeline data
     *
     * @param array $data
     * @param string $start
     * @param string $end
     * @param string $step
     * @param string $unit
     * @return array
     */
    protected static function fill(array $data, string $start, string $end, string $step, string $unit): array
    {
        for (
            $i = strtotime($start);
            $i <= strtotime($end);
            $i = strtotime(date('Y-m-d H:i:s', $i) . ' +1 ' . $step)
        ) {
            $label = strftime($unit, $i);

            if (in_array($label, array_column($data, 'label')) === false) {
                array_push($data, [
                    'label'      => $label,
                    'time'       => $i,
                    'total'      => 0,
                    'redirected' => 0,
                    'resolved'   => 0
                ]);
            }
        }

        return $data;
    }

    /**
     * Generate human readable title for timeframe
     *
     * @param string $start
     * @param string $end
     * @return string
     */
    protected static function title(string $start, string $end): string
    {
        $start = strtotime($start);
        $end   = strtotime($end);

        // whole day
        if (date('Y-m-d', $start) === date('Y-m-d', $end)) {
            return strftime('%e %B %Y', $end);
        }

        // whole month
        if (
            date('Y-m', $start) === date('Y-m', $end) &&
            date('j', $start) === '1' &&
            date('j', $end) === date('t', $end)
        ) {
            return strftime('%B %Y', $end);
        }

        // whole year
        if (
            date('Y', $start) === date('Y', $end) &&
            date('j', $start) === '1' &&
            date('n', $start) === '1' &&
            date('j', $end) === '31' &&
            date('n', $end) === '12'
        ) {
            return strftime('%Y', $start);
        }

        // days, same month
        if (
            date('m', $start) === date('m', $end) &&
            date('Y', $start) === date('Y', $end)
        ) {
            return strftime('%e', $start) . '-' . strftime('%e %B %Y', $end);
        }

        // detailed date
        if (date('Y', $start) === date('Y', $end)) {
            return strftime('%e %B', $start) . ' - ' . strftime('%e %B %Y', $end);
        }

        return strftime('%e %B %Y', $start) . ' - ' . strftime('%e %B %Y', $end);
    }

}
