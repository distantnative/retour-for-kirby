<?php

namespace distantnative\Retour;

class Stats
{

    /**
     * Get stats data for specified timeframe and type
     *
     * @param string $unit  timeframe unit (year, month, ...)
     * @param string $from  date sting (yyyy-mm-dd)
     * @param string $to    date sting (yyyy-mm-dd)
     *
     * @return array
     */
    public static function get(string $unit, string $from, string $to): array
    {
        $log  = new Log;

        // Add time to dates to capture full days
        $from .= ' 00:00:00';
        $to   .= ' 23:59:59';

        // Define formats based on timeframe unit
        switch ($unit) {
            case 'year':
                $formats = ['month', '%m'];
                break;
            case 'month':
                $formats = ['day', '%d'];
                break;
            case 'day':
                $formats = ['hour', '%H'];
                break;
            default:
                $formats = ['day', '%d/%m'];
                break;
        }

        // Get data from database
        $data = $log->forStats($from, $to, $formats[1]);

        // Fill the holes
        $data = self::fill($data, $from, $to, ...$formats);

        return $data;
    }

    /**
     * Fill gaps in timeline data
     *
     * @param array  $data
     * @param string $start  date sting (yyyy-mm-dd hh:mm:ss)
     * @param string $end    date sting (yyyy-mm-dd hh:mm:ss)
     * @param string $step   step for strtotime (day, month, etc.)
     * @param string $unit   strftime string (e.g. %m)
     *
     * @return array
     */
    protected static function fill(array $data, string $start, string $end, string $step, string $unit): array
    {
        // Run loop from the start date until the end date
        // increasing by a step each run (e.g. +1 day, +1 month...)
        for (
            $date = strtotime($start);
            $date <= strtotime($end);
            $date = strtotime(date('Y-m-d H:00:00', $date) . ' +1 ' . $step)
        ) {
            // Create label for hole
            $label = strftime($unit, $date);

            // Check if label is already in array
            // (data entry exists, no hole)
            if (in_array($label, array_column($data, 'label')) === false) {

                // Add empty value set to fill hole
                $data[] = [
                    'label'      => $label,
                    'time'       => $date,
                    'total'      => 0,
                    'redirected' => 0,
                    'resolved'   => 0
                ];
            }
        }

        // Sort all entries by time field
        usort($data, function ($item1, $item2) {
            return $item1['time'] <=> $item2['time'];
        });

        return $data;
    }
}
