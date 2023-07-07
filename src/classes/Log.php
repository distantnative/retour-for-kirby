<?php

namespace distantnative\Retour;

use Kirby\Database\Database;
use Kirby\Database\Query;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

/**
 * Log
 * Reads and writes to the database log
 * for all 404 requests or successful redirects
 *
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */
class Log
{
    protected Database $database;

    public function __construct(
        protected Plugin $plugin
    ) {
        // get path to database file
        $default = kirby()->root('logs') . '/retour/log.sqlite';
        $file    = $this->plugin()->option('database', $default);

        // support callbacks for database file option
        if (is_callable($file) === true) {
            $file = $file();
        }

        // make sure database is in place
        if (F::exists($file) === false) {
            $dir = dirname($file);

            if (is_dir($dir) === false) {
                Dir::make($dir);
            }

            // copy empty database file into place
            F::copy(dirname(__DIR__, 2) . '/assets/retour.sqlite', $file);
        }

        // connect to database
        $this->database = new Database([
            'type'     => 'sqlite',
            'database' => $file
        ]);
    }

    /**
     * Create a new record entry in database
     */
    public function add(array $props): int|false
    {
        return $this->table()->insert([
            'date'     => $props['date'] ?? date('Y-m-d H:i:s'),
            'path'     => $props['path'],
            'referrer' => $props['referrer'] ?? $_SERVER['HTTP_REFERER'] ?? null,
            'redirect' => $props['redirect'] ?? null
        ]);
    }

    /**
     * Returns all logged 404s
     *
     * @param string $from date sting (yyyy-mm-dd)
     * @param string $to date sting (yyyy-mm-dd)
     */
    public function fails(string $from, string $to): array
    {
        // Add time to dates to capture full days
        $from .= ' 00:00:00';
        $to   .= ' 23:59:59';

        // Run query
        $fails = $this->table()
            ->select('
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

        return $fails->toArray(fn (array $entry) => [
            'path'     => $entry['path'],
            'referrer' => $entry['referrer'],
            'last'     => $entry['last'],
            'hits'     => (int)$entry['hits'],
        ]);
    }

    /**
     * Returns the first log entry
     */
    public function first(): array
    {
        return $this->single('date ASC');
    }


    /**
     * Remove database records and reset index
     */
    public function flush(): bool
    {
        $table = $this->table()->delete();
        $index = $this->database->sqlite_sequence()->delete([
            'name' => 'records'
        ]);
        return $table && $index;
    }

    /**
     * Returns the last log entry
     */
    public function last(): array
    {
        return $this->single('date DESC');
    }

    /**
     * Returns the Plugin instance
     */
    public function plugin(): Plugin
    {
        return $this->plugin;
    }

    /**
     * Deletes outdated logs based on config option
     */
    public function purge(): bool
    {
        // Get limit (in months) from option
        $limit = $this->plugin()->option('deleteAfter', false);

        if ($limit !== false) {
            // Get cutoff date by subtracting limit from today
            $time   = strtotime('-' . $limit . ' month');
            $cutoff = date('Y-m-d 00:00:00', $time);

            /** @var \Kirby\Database\Query $table */
            $table = $this->table()->bindings(['cutoff' => $cutoff]);

            return $table->delete('strftime("%s", date) < strftime("%s", :cutoff)');
        }

        return true;
    }

    /**
     * Returns all records for a redirect
     *
     * @param string $path redirect path
     * @param string $from date sting (yyyy-mm-dd)
     * @param string $to date sting (yyyy-mm-dd)
     */
    public function redirect(string $path, string $from, string $to): array
    {
        // Add time to dates to capture full days
        $from .= ' 00:00:00';
        $to   .= ' 23:59:59';

        // Run query
        /** @var array */
        $data = $this->table()
            ->select('
                COUNT(*) AS hits,
                MAX(date) AS last
            ')
            ->where(['redirect' => $path])
            ->andWhere('strftime("%s", date) > strftime("%s", :start)', ['start' => $from])
            ->andWhere('strftime("%s", date) < strftime("%s", :end)', ['end' => $to])
            ->fetch('array')
            ->first();

        return [
            'hits' => (int)$data['hits'],
            'last' => $data['last']
        ];
    }

    /**
     * Remove an entry from the database
     */
    public function remove(string $path): bool
    {
        return $this->table()->delete(
            'path = "' . $this->database->escape($path) . '"'
        );
    }

    /**
     * Mark all records for a given path as resolved
     */
    public function resolve(string $path): bool
    {
        return $this->table()->update(
            ['wasResolved' => 1],
            ['path' => $path]
        );
    }

    /**
     * Returns stats data for specified timeframe and unit
     *
     * @param string $unit timeframe unit (year, month, ...)
     * @param string $from date sting (yyyy-mm-dd)
     * @param string $to date sting (yyyy-mm-dd)
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
            case 'months':
                $use['group'] = '%Y-%m';
                $use['step']  = 'month';
                break;
        }
        // Get data from database
        $data = $this->database->query('
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

        return $data->toArray(fn (array $entry) => [
            'date'       => (string)$entry['date'],
            'failed'     => (int)$entry['failed'],
            'resolved'   => (int)$entry['resolved'],
            'redirected' => (int)$entry['redirected'],
        ]);
    }

    /**
     * Returns the database table object
     */
    public function table(): Query
    {
        return $this->database->records();
    }

    /**
     */
    protected function single(string $sort): array
    {
        /** @var array|false */
        $result = $this->table()
            ->select('date')
            ->order($sort)
            ->fetch('array')
            ->first();

        if ($result === false) {
            return [];
        }

        return $result;
    }
}
