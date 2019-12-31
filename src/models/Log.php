<?php

namespace distantnative\Retour;

use Kirby\Database\Database;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

class Log
{

    /**
     * Database connection
     *
     * @var \Kirby\Database\Database;
     */
    protected $db;

    public function __construct()
    {
        // Make sure database is in place
        $this->setup();

        // Connect to database
        $this->db = new Database([
            'type'     => 'sqlite',
            'database' => Retour::root('logs')
        ]);
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
        return $this->db->records()->insert([
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
        //
        $stats = $this->db->records()
            ->select('
                strftime(:label, date) AS label,
                strftime("%s", MIN(date)) AS time,
                COUNT(path) AS total,
                COUNT(wasResolved) - COUNT(wasResolved + redirect) AS resolved,
                COUNT(redirect) AS redirected
            ', ['label' => $label])
            ->where('strftime("%s", date) > strftime("%s", :start)', ['start' => $start])
            ->andWhere('strftime("%s", date) < strftime("%s", :end)', ['end' => $end])
            ->group('label')
            ->order('time')
            ->fetch('array')
            ->all();

        if ($stats === false) {
            return [];
        }

        // Ensure proper types for data values
        return $stats->toArray(function ($entry) {
            return [
                'label'      => (string) $entry['label'],
                'time'       => (int)    $entry['time'],
                'total'      => (int)    $entry['total'],
                'resolved'   => (int)    $entry['resolved'],
                'redirected' => (int)    $entry['redirected'],
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

            return $this->db->records()->delete('strftime("%s", date) < strftime("%s", :cutoff)', ['cutoff' => $cutoff]);
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
        return $this->db->update(
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
