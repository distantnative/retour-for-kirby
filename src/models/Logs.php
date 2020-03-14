<?php

namespace distantnative\Retour;

use Kirby\Database\Database;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

class Logs
{

    /**
     * Database connection
     *
     * @param \Kireby\Retour\Retour $retour
     * @var \Kirby\Database\Database;
     */
    protected $db;

    public function __construct(Retour $retour)
    {
        // Make sure database is in place
        $file = $retour::root('logs');
        $dir  = dirname($file);

        if (F::exists($file) === false) {
            if (is_dir($dir) === false) {
                Dir::make($dir);
            }

            F::copy(
                $retour::root('assets') . '/retour.sqlite',
                $file
            );
        }

        // Connect to database
        $this->db = new Database([
            'type' => 'sqlite',
            'database' => $retour::root('logs')
        ]);
    }

    /**
     * Create a new record entry in database
     *
     * @param array $props
     *
     * @return bool
     */
    public function create(array $props): bool
    {
        return $this->db->records()->insert([
            'date'     => $props['date'] ?? date('Y-m-d H:i:s'),
            'path'     => $props['path'],
            'referrer' => $props['referrer'] ?? $_SERVER['HTTP_REFERER'] ?? null,
            'redirect' => $props['redirect'] ?? null
        ]);
    }

    /**
     * Get all failed records
     *
     * @param string $start  date sting (yyyy-mm-dd)
     * @param string $end    date sting (yyyy-mm-dd)
     *
     * @return array
     */
    public function fails(string $start, string $end): array
    {
        // Add time to dates to capture full days
        $start .= ' 00:00:00';
        $end   .= ' 23:59:59';

        // Run query
        $fails = $this->db->records()
            ->select('
                id,
                path,
                referrer,
                MAX(date) AS last,
                COUNT(date) AS hits
            ')
            ->where('redirect IS NULL')
            ->andWhere('wasResolved IS NULL')
            ->andWhere('strftime("%s", date) > strftime("%s", :start)', ['start' => $start])
            ->andWhere('strftime("%s", date) < strftime("%s", :end)', ['end' => $end])
            ->group('path, referrer')
            ->fetch('array')
            ->all();

        if ($fails === false) {
            return [];
        }

        return $fails->toArray();
    }


    /**
     * Remove database records and reset index
     *
     * @return bool
     */
    public function flush(): bool
    {
        $table = $this->db->records()->delete();
        $index = $this->db->sqlite_sequence()->delete(['name' => 'records']);
        return $table && $index;
    }

    /**
     * Deletes outdated logs based on config option
     *
     * @return bool
     */
    public function purge(): bool
    {
        // Get limit (in months) from option
        $limit = option('distantnative.retour.deleteAfter');

        if ($limit !== false) {
            // Get cutoff date by subtracting limit from today
            $time   = strtotime('-' . $limit . ' month');
            $cutoff = date('Y-m-d 00:00:00', $time);

            return $this->db->records()->delete('strftime("%s", date) < strftime("%s", :cutoff)', ['cutoff' => $cutoff]);
        }

        return true;
    }

    /**
     * Get all records for a redirect
     *
     * @param array $redirect  redirect array
     * @param string $start    date sting (yyyy-mm-dd)
     * @param string $end      date sting (yyyy-mm-dd)
     *
     * @return array
     */
    public function redirect(array $redirect, string $start, string $end): array
    {
        // Add time to dates to capture full days
        $start .= ' 00:00:00';
        $end   .= ' 23:59:59';

        // Run query
        $data = $this->db->records()
            ->select('
                COUNT(*) AS hits,
                MAX(date) AS last
            ')
            ->where(['redirect' => $redirect['from']])
            ->andWhere('strftime("%s", date) > strftime("%s", :start)', ['start' => $start])
            ->andWhere('strftime("%s", date) < strftime("%s", :end)', ['end' => $end])
            ->fetch('array')->first();

        if ($data === false) {
            return $redirect;
        }

        // Add stats data to original redirect data
        return array_merge($redirect, $data);
    }

    /**
     * Mark all records for a given path as resolved
     *
     * @param string $path
     *
     * @return bool
     */
    public function resolve(string $path): bool
    {
        return $this->db->update(
            'records',
            ['wasResolved' => 1],
            ['path' => $path]
        );
    }

    /**
     * Get stats data for specified timeframe and type
     *
     * @param string $unit  timeframe unit (year, month, ...)
     * @param string $from  date sting (yyyy-mm-dd)
     * @param string $to    date sting (yyyy-mm-dd)
     *
     * @return array
     */
    public function stats(string $unit, string $from, string $to): array
    {
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
        $data = $this->db->records()
            ->select('
                strftime(:label, date) AS label,
                strftime("%s", MIN(date)) AS time,
                COUNT(path) AS total,
                COUNT(wasResolved) - COUNT(wasResolved + redirect) AS resolved,
                COUNT(redirect) AS redirected
            ', ['label' => $formats[1]])
            ->where('strftime("%s", date) > strftime("%s", :start)', ['start' => $from])
            ->andWhere('strftime("%s", date) < strftime("%s", :end)', ['end' => $to])
            ->group('label')
            ->order('time')
            ->fetch('array')
            ->all();

        if ($data === false) {
            $data = [];

        } else {
            // Ensure proper types for data values
            $data = $data->toArray(function ($entry) {
                return [
                    'label'      => (string) $entry['label'],
                    'time'       => (int)    $entry['time'],
                    'total'      => (int)    $entry['total'],
                    'resolved'   => (int)    $entry['resolved'],
                    'redirected' => (int)    $entry['redirected'],
                ];
            });
        }

        // Fill the holes:
        // Run loop from the start date until the end date
        // increasing by a step each run (e.g. +1 day, +1 month...)
        for (
            $date = strtotime($from);
            $date <= strtotime($to);
            $date = strtotime(date('Y-m-d H:00:00', $date) . ' +1 ' . $formats[0])
        ) {
            // Create label for hole
            $label = strftime($formats[1], $date);

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