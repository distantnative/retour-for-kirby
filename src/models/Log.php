<?php

namespace distantnative\Retour;

use Kirby\Database\Db;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

class Log
{

    public function __construct()
    {
        $this->setup();
        $this->connect();
    }

    /**
     * Create a new record entry in database
     *
     * @param array $props
     *
     * @return bool
     */
    public function add(array $props): bool
    {
        return Db::insert('records', [
            'date'     => $props['date'] ?? date('Y-m-d H:i:s'),
            'path'     => $props['path'],
            'referrer' => $props['referrer'] ?? $_SERVER['HTTP_REFERER'] ?? null,
            'redirect' => $props['redirect'] ?? null
        ]);
    }

    /**
     * Close database connection
     *
     * @return void
     */
    public function close(): void
    {
        Db::$connection = null;
    }

    /**
     * Connects database
     *
     * @return void
     */
    protected function connect(): void
    {
        Db::connect([
            'type'     => 'sqlite',
            'database' => Retour::root('logs')
        ]);
    }

    /**
     * Remove database records and reset index
     *
     * @return bool
     */
    public function flush(): bool
    {
        return Db::execute('
            DELETE FROM records;
            DELETE FROM sqlite_sequence WHERE name="records";
        ');
    }

    /**
     * Get all failed records
     *
     * @param string $start  date sting (yyyy-mm-dd)
     * @param string $end    date sting (yyyy-mm-dd)
     *
     * @return array
     */
    public function forFails(string $start, string $end): array
    {
        // Add time to dates to capture full days
        $start .= ' 00:00:00';
        $end   .= ' 23:59:59';

        // Run query
        $fails = Db::query('
            SELECT
                id,
                path,
                referrer,
                MAX(date) AS last,
                COUNT(date) AS hits
            FROM
                records
            WHERE
                redirect IS NULL AND
                wasResolved IS NULL AND
                strftime("%s", date) > strftime("%s", "' . $start . '") AND
                strftime("%s", date) < strftime("%s", "' . $end . '")
            GROUP BY
                path,
                referrer;
        ');

        return $fails ? $fails->toArray() : [];
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
    public function forRedirect(array $redirect, string $start, string $end): array
    {
        // Add time to dates to capture full days
        $start .= ' 00:00:00';
        $end   .= ' 23:59:59';

        // Run query
        $data = Db::query('
            SELECT
                COUNT(*) AS hits,
                MAX(date) AS last
            FROM
                records
            WHERE
                redirect="' . $redirect['from'] . '" AND
                strftime("%s", date) > strftime("%s", "' . $start . '") AND
                strftime("%s", date) < strftime("%s", "' . $end . '")
        ')->first();

        // Add stats data to original redirect data
        return array_merge($redirect, [
            'hits' => (int)    $data->hits(),
            'last' => (string) $data->last()
        ]);
    }

    /**
     * Return aggregated data as timeline
     *
     * @param string $start  date sting (yyyy-mm-dd hh:mm:ss)
     * @param string $end    date sting (yyyy-mm-dd hh:mm:ss)
     * @param string $label  strftime string (e.g. %m)
     *
     * @return array
     */
    public function forStats(string $start, string $end, string $label): array
    {
        $stats = Db::query('
            SELECT
                strftime("' . $label . '", date) AS label,
                strftime("%s", MIN(date)) AS time,
                COUNT(path) AS total,
                COUNT(wasResolved) - COUNT(wasResolved + redirect) AS resolved,
                COUNT(redirect) AS redirected
            FROM
                records
            WHERE
                strftime("%s", date) > strftime("%s", "' . $start . '") AND
                strftime("%s", date) < strftime("%s", "' . $end . '")
            GROUP BY
                label
            ORDER BY
                time;
        ');

        // Return empty array if query failed
        if ($stats === false) {
            return [];
        }

        // Ensure proper types for data values
        return $stats->toArray(function ($entry) {
            return [
                'label'      => (string) $entry->label(),
                'time'       => (int)    $entry->time(),
                'total'      => (int)    $entry->total(),
                'resolved'   => (int)    $entry->resolved(),
                'redirected' => (int)    $entry->redirected(),
            ];
        });
    }

    /**
     * Deletes outdated logs based on config option
     *
     * @return bool
     */
    public function limit(): bool
    {
        // Get limit (in months) from option
        $limit = option('distantnative.retour.deleteAfter');

        if ($limit !== false) {
            // Get cutoff date by subtracting limit from today
            $time   = strtotime('-' . $limit . ' month');
            $cutoff = date('Y-m-d 00:00:00', $time);

            return Db::query('
                DELETE FROM
                    records
                WHERE
                    strftime("%s", date) < strftime("%s", "' . $cutoff . '");
            ');
        }

        return true;
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
        return Db::update(
            'records',
            ['wasResolved' => 1],
            ['path' => $path]
        );
    }

    /**
     * Copies database in right location if does not exist yet
     *
     * @return void
     */
    public function setup(): void
    {
        $file = Retour::root('logs');
        $dir  = dirname($file);

        if (F::exists($file) === false) {
            if (is_dir($dir) === false) {
                Dir::make($dir);
            }

            F::copy(
                Retour::root('assets') . '/retour.sqlite',
                $file
            );
        }
    }
}
