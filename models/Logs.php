<?php

namespace distantnative\Retour;

use Kirby\Database\Database;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

class Logs
{

    /**
     * @var \Kirby\Database\Database;
     */
    protected $db;

    /**
     * @var string
     */
    protected $file;

    public function __construct()
    {
        // Get path to database file
        $this->file = option('distantnative.retour.database');

        if (is_callable($this->file) === true) {
            $this->file = call_user_func($this->file);
        }

        // Make sure database is in place
        if (F::exists($this->file) === false) {
            $dir = dirname($this->file);

            if (is_dir($dir) === false) {
                Dir::make($dir);
            }

            F::copy(
                dirname(__DIR__) . '/assets/retour.sqlite',
                $this->file
            );
        }

        // Connect to database
        $this->db = new Database([
            'type' => 'sqlite',
            'database' => $this->file
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

    public function first(): array
    {
        return $this->db->records()
            ->select('date')
            ->order('date ASC')
            ->fetch('array')
            ->first();
    }

    public function last(): array
    {
        return $this->db->records()
            ->select('date')
            ->order('date DESC')
            ->fetch('array')
            ->first();
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
        return $this->db->records()->update(
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
        // Define parts depending on uni
         $use = [
            'func'  => 'date',
            'group' => '%Y-%m-%d',
            'step'  => 'day'
         ];

        switch ($unit) {
            case 'day':
                // Add time to dates to capture full days
                $from .= ' 00:00:00';
                $to   .= ' 23:59:59';

                $use['func']  = 'datetime';
                $use['group'] = '%Y-%m-%d %H';
                $use['step']  = 'hour';
                break;

            case 'year':
                $use['group'] = '%Y-%m';
                $use['step']  = 'month';
                break;
        }
        // Get data from database
        $data = $this->db->query('
            with recursive dates as (
                select :from as date
                union all
                select ' . $use['func'] . '(date, "+1 ' . $use['step'] . '") from dates where date < :to
            )
            SELECT
                dates.date,
                COUNT(redirect) AS redirected,
                COUNT(wasResolved) - COUNT(wasResolved + redirect) AS resolved,
                COUNT(path) - COUNT(wasResolved + redirect) - COUNT(redirect) AS failed
            FROM
                dates
            LEFT JOIN
                records
            ON
                strftime(:group, dates.date) = strftime(:group, records.date)
            WHERE
                strftime("%s", dates.date) >= strftime("%s", :from)
            AND
                strftime("%s", dates.date) <= strftime("%s", :to)
            GROUP BY
                strftime(:group, dates.date)
            ORDER BY
                strftime(:group, dates.date)
        ', [
            'from'  => $from,
            'to'    => $to,
            'group' => $use['group']
        ], [
            'fetch' => 'array'
        ]);

        if ($data === false) {
            return [];

        }

        return $data->toArray(function ($entry) {
            return [
                'date'       => (string) $entry['date'],
                'failed'     => (int)    $entry['failed'],
                'resolved'   => (int)    $entry['resolved'],
                'redirected' => (int)    $entry['redirected'],
            ];
        });
    }
}
