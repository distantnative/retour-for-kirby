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
    public static function get(string $by, string $from, string $to): array
    {
        $log = new Log;

        switch ($by) {
            case 'year':
                $data = $log->forStats($from, $to, '%m');
                $data = self::fill($data, $from, $to, 'month', '%m');
                break;

            case 'month':
                $data = $log->forStats($from, $to, '%d');
                $data = self::fill($data, $from, $to, 'day', '%d');
                break;

            case 'day':
                $data = $log->forStats($from, $to, '%H');
                $data = self::fill($data, $from, $to, 'hour', '%H');
                break;

            case 'custom':
            case 'week':
                $data = $log->forStats($from, $to, '%d/%m');
                $data = self::fill($data, $from, $to, 'day', '%d/%m');
                break;
        }

        return $data;
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
}
