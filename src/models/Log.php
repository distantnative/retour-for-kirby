<?php

namespace distantnative\Retour;

use Kirby\Database\Db;

class Log
{

    /**
     * Connect to database on initialization
     */
    public function __construct()
    {
        $this->setup();
        $this->connect();
    }

    /**
     * Create a new record entry in database
     *
     * @param array $props
     * @return void
     */
    public function add(array $props): void
    {
        Db::insert('records', [
            'date'     => $props['date'] ?? date('Y-m-d H:i:s'),
            'timezone' => date('Z'),
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
     * @return void
     */
    public function flush(): void
    {
        Db::execute('DELETE FROM records;');
        Db::execute('DELETE FROM sqlite_sequence WHERE name="records";');
    }

    /**
     * Get all failed records
     *
     * @return array
     */
    public function forFails(): array
    {
        return Db::query('
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
                wasResolved IS NULL
            GROUP BY
                path,
                referrer;
        ')->toArray();
    }

    /**
     * Get all records for a redirect
     *
     * @param array $redirect
     * @return array
     */
    public function forRedirect(array $redirect): array
    {
        $data = Db::first('records', '
            COUNT(*) AS hits,
            MAX(date) AS last'
        , 'redirect="' . $redirect['from'] . '"');

        return array_merge($redirect, [
            'hits' => $data->hits(),
            'last' => $data->last()
        ]);
    }

    /**
     * Return aggregated data as timeline
     *
     * @param string $start
     * @param string $end
     * @param string $label
     * @return array
     */
    public function forStats(string $start, string $end, string $label): array
    {
        return Db::query('
            SELECT
                strftime("' . $label . '", date) AS label,
                strftime("%s", MIN(date)) - timezone AS time,
                COUNT(path) AS total,
                COUNT(wasResolved) - COUNT(wasResolved + redirect) AS resolved,
                COUNT(redirect) AS redirected
            FROM
                records
            WHERE
                strftime("%s", date) > strftime("%s", "' . $start . '") AND
                strftime("%s", date) < strftime("%s", "' . $end . '")
            GROUP BY
                label;
        ')->toArray();
    }

    public function limit(): void
    {
        $limit  = option('retour.deleteAfter', false);

        if ($limit) {
            $time   = strtotime('-' . $limit . ' month');
            $cutoff = date('Y-m-d 00:00:00', $time);

            Db::query('
                DELETE FROM
                    records
                WHERE
                    strftime("%s", date) < strftime("%s", "' . $cutoff . '");
            ');
        }
    }

    /**
     * Mark all records for a given path as resolved
     *
     * @param string $path
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

    public function setup(): void
    {
        $file = Retour::root('logs');
        if (file_exists($file) === false) {
            F::copy(
                Retour::root('assets') . '/retour.sqlite',
                $file
            );
        }
    }
}
