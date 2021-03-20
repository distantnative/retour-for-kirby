<?php

namespace distantnative\Retour;

use Kirby\Database\Database;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

class Log
{
    /**
     * @var \Kirby\Database\Database
     */
    protected $db;

    public function __construct()
    {
        // Get path to database file
        $default = kirby()->root('logs') . '/retour/log.sqlite';
        $file    = option('distantnative.retour.database', $default);

        // Support callbacks for database file option
        if (is_callable($file) === true) {
            $file = call_user_func($file);
        }

        // Make sure database is in place
        if (F::exists($file) === false) {
            $dir = dirname($file);

            if (is_dir($dir) === false) {
                Dir::make($dir);
            }

            F::copy(dirname(__DIR__) . '/assets/retour.sqlite', $file);
        }

        // Connect to database
        $this->db = new Database([
            'type'     => 'sqlite',
            'database' => $file
        ]);
    }

    /**
     * Create a new record entry in database
     *
     * @param array $props
     * @return bool
     */
    public function add(array $props): bool
    {
        return $this->db->records()->insert([
            'date'     => $props['date'] ?? date('Y-m-d H:i:s'),
            'path'     => $props['path'],
            'referrer' => $props['referrer'] ?? $_SERVER['HTTP_REFERER'] ?? null,
            'redirect' => $props['redirect'] ?? null
        ]);
    }

    protected function single(string $sort): array
    {
        $result = $this->db->records()
            ->select('date')
            ->order($sort)
            ->fetch('array')
            ->first();

        if ($result) {
            return $result;
        }

        return [];
    }

    public function first(): array
    {
        return $this->single('date ASC');
    }

    public function last(): array
    {
        return $this->single('date DESC');
    }

    /**
     * Get all failed records
     *
     * @param string $from date sting (yyyy-mm-dd)
     * @param string $to date sting (yyyy-mm-dd)
     *
     * @return array
     */
    public function fails(string $from, string $to): array
    {
        // Add time to dates to capture full days
        $from .= ' 00:00:00';
        $to   .= ' 23:59:59';

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
            ->andWhere('strftime("%s", date) > strftime("%s", :start)', ['start' => $from])
            ->andWhere('strftime("%s", date) < strftime("%s", :end)', ['end' => $to])
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
        $limit = Retour::meta()['deleteAfter'];

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
     * @param array $redirect redirect array
     * @param string $from date sting (yyyy-mm-dd)
     * @param string $to date sting (yyyy-mm-dd)
     *
     * @return array
     */
    public function redirect(string $path, string $from, string $to): array
    {
        // Add time to dates to capture full days
        $from .= ' 00:00:00';
        $to   .= ' 23:59:59';

        // Run query
        $data = $this->db->records()
            ->select('
                COUNT(*) AS hits,
                MAX(date) AS last
            ')
            ->where(['redirect' => $path])
            ->andWhere('strftime("%s", date) > strftime("%s", :start)', ['start' => $from])
            ->andWhere('strftime("%s", date) < strftime("%s", :end)', ['end' => $to])
            ->fetch('array')->first();

        if ($data === false) {
            return [];
        }

        return $data;
    }

    /**
     * Remove an entry from the log database
     *
     * @param string $path
     * @param string $referrer
     *
     * @return bool
     */
    public function remove(string $path, string $referrer = null): bool
    {
        $where = 'path = "' . $this->db->escape($path) . '" AND referrer ';

        if ($referrer === null) {
            $where .= 'IS NULL';
        } else {
            $where .= '= "' . $this->db->escape($referrer) . '"';
        }

        return $this->db->records()->delete($where);
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
     * Get stats data for specified timeframe and unit
     *
     * @param string $unit timeframe unit (year, month, ...)
     * @param string $from date sting (yyyy-mm-dd)
     * @param string $to date sting (yyyy-mm-dd)
     *
     * @return array
     */
    public function stats(string $unit, string $from, string $to): array
    {
        // Define parts depending on unit
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
                'date'       => (string)$entry['date'],
                'failed'     => (int)$entry['failed'],
                'resolved'   => (int)$entry['resolved'],
                'redirected' => (int)$entry['redirected'],
            ];
        });
    }
}

class LogDisabled
{
    public function __call(string $property, array $arguments)
    {
        return false;
    }
}
