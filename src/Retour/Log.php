<?php

namespace Kirby\Retour;

use Closure;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Kirby\Database\Database;
use Kirby\Database\Query;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

/**
 * Reads and writes to the database log
 * for all 404 requests or successful redirects
 */
class Log
{
	protected Database $database;

	public function __construct(
		protected Retour $retour
	) {
		// get path to database file
		$default = $retour->kirby()->root('logs') . '/retour/log.sqlite';
		$file    = $retour->option('database', $default);

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
	 * @since 5.6.0
	 */
	public function database(): Database
	{
		return $this->database;
	}

	/**
	 * Returns all logged 404s
	 *
	 * @param $from date string (yyyy-mm-dd)
	 * @param $to date string (yyyy-mm-dd)
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
                COUNT(*) AS hits
            ')
			->where('redirect IS NULL')
			->andWhere('wasResolved IS NULL')
			->andWhere('date >= :start', ['start' => $from])
			->andWhere('date <= :end', ['end' => $to])
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
	 *
	 * @param $mode `all` or `failures`
	 * @throws InvalidArgumentException If the mode is unknown
	 */
	public function flush(string $mode = 'all'): bool
	{
		$deleted = match ($mode) {
			'all'      => $this->table()->delete(),
			'failures' => $this->table()->delete('redirect IS NULL AND wasResolved IS NULL'),
			default    => throw new InvalidArgumentException(
				message: 'Invalid flush mode: ' . $mode
			)
		};

		if ($mode === 'all') {
			$this->database->sqlite_sequence()->delete(['name' => 'records']);
		}

		$vacuum = $this->database->execute('VACUUM');
		return $deleted && $vacuum;
	}

	/**
	 * Returns the last log entry
	 */
	public function last(): array
	{
		return $this->single('date DESC');
	}

	/**
	 * Deletes outdated logs based on config option
	 */
	public function purge(): bool
	{
		// Get limit (in months) from option
		$limit = $this->retour->option('deleteAfter', false);

		if ($limit !== false) {
			// Get cutoff date by subtracting limit from today
			$time = strtotime('-' . $limit . ' month');

			if ($time === false) {
				return false;
			}

			$cutoff = date('Y-m-d 00:00:00', $time);

			/** @var Query $table */
			$table = $this->table()->bindings(['cutoff' => $cutoff]);

			$delete = $table->delete('date < :cutoff');

			// only rebuild the database file
			// when records have been deleted
			if ($this->database->affected() === 0) {
				return $delete;
			}

			$vacuum = $this->database->execute('VACUUM');
			return $delete && $vacuum;
		}

		return true;
	}

	/**
	 * Returns all records for a redirect
	 *
	 * @param $path redirect path
	 * @param $from date string (yyyy-mm-dd)
	 * @param $to date string (yyyy-mm-dd)
	 */
	public function redirect(string $path, string $from, string $to): array
	{
		// Add time to dates to capture full days
		$from .= ' 00:00:00';
		$to   .= ' 23:59:59';

		// Run query
		/** @var array|false */
		$data = $this->table()
			->select('
                COUNT(*) AS hits,
                MAX(date) AS last
            ')
			->where(['redirect' => $path])
			->andWhere('date >= :start', ['start' => $from])
			->andWhere('date <= :end', ['end' => $to])
			->fetch('array')
			->first();

		return [
			'hits' => (int)($data['hits'] ?? 0),
			'last' => $data['last'] ?? null
		];
	}

	/**
	 * Returns hits and last hit of all redirects in one query,
	 * keyed by redirect path; redirects without hits are missing
	 *
	 * @param $from date string (yyyy-mm-dd)
	 * @param $to date string (yyyy-mm-dd)
	 * @return array<string, array{hits: int, last: string}>
	 */
	public function redirects(string $from, string $to): array
	{
		// Add time to dates to capture full days
		$from .= ' 00:00:00';
		$to   .= ' 23:59:59';

		$data = $this->table()
			->select('
                redirect,
                COUNT(*) AS hits,
                MAX(date) AS last
            ')
			->where('redirect IS NOT NULL')
			->andWhere('date >= :start', ['start' => $from])
			->andWhere('date <= :end', ['end' => $to])
			->group('redirect')
			->fetch('array')
			->all();

		$redirects = [];

		foreach ($data as $entry) {
			$redirects[$entry['redirect']] = [
				'hits' => (int)$entry['hits'],
				'last' => $entry['last']
			];
		}

		return $redirects;
	}

	/**
	 * Remove an entry from the database
	 */
	public function remove(string $path): bool
	{
		return $this->table()->delete(['path' => $path]);
	}

	/**
	 * Removes all failures whose path passes the filter,
	 * e.g. when ignoring these paths from now on
	 *
	 * @param Closure(string $path): bool $filter
	 */
	public function removeFailures(Closure $filter): bool
	{
		$paths = $this->table()
			->select('path')
			->distinct()
			->where('redirect IS NULL')
			->fetch('array')
			->all()
			->toArray(fn (array $record) => $record['path']);

		$paths = array_values(array_filter($paths, $filter));

		// delete in chunks to stay below
		// the SQLite limit for bound parameters
		foreach (array_chunk($paths, 500) as $chunk) {
			$deleted = $this->table()
				->where('redirect IS NULL')
				->andWhere('path', 'in', $chunk)
				->delete();

			if ($deleted === false) {
				return false;
			}
		}

		return true;
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

	/**
	 * Returns stats data for specified timeframe and unit
	 *
	 * @param $unit timeframe unit (year, month, ...)
	 * @param $from date string (yyyy-mm-dd)
	 * @param $to date string (yyyy-mm-dd)
	 */
	public function stats(string $unit, string $from, string $to): array
	{
		// Define parts depending on unit,
		// grouping by the start of the date string
		// is faster than formatting each date
		$use = [
			'length'    => 10,
			'group_php' => 'Y-m-d',
			'step'      => new DateInterval('P1D')
		];

		$overrides = match ($unit) {
			'day' => [
				'length'    => 13,
				'group_php' => 'Y-m-d H',
				'step'      => new DateInterval('PT1H'),
			],
			'year', 'months' => [
				'length'    => 7,
				'group_php' => 'Y-m',
				'step'      => new DateInterval('P1M'),
			],
			default => [],
		};

		$use = array_merge($use, $overrides);

		// Get data from database
		$data = $this->database->query('
            SELECT
                substr(date, 1, :length) AS date,
				SUM(CASE WHEN redirect IS NOT NULL THEN 1 END) AS redirected,
				SUM(CASE WHEN wasResolved IS NOT NULL AND redirect IS NULL THEN 1 END) AS resolved,
				SUM(CASE WHEN redirect IS NULL AND wasResolved IS NULL THEN 1 END) AS failed
            FROM
                records
            WHERE
                date >= :from
            AND
                date <= :to
			GROUP BY
                substr(date, 1, :length)
            ORDER BY
                substr(date, 1, :length)
        ', [
			// Add time to dates to capture full days
			'from'   => $from . ' 00:00:00',
			'to'     => $to . ' 23:59:59',
			'length' => $use['length']
		], [
			'fetch' => 'array'
		]);

		$data = $data->toArray(fn (array $entry) => [
			'date'       => (string)$entry['date'],
			'failed'     => (int)$entry['failed'],
			'resolved'   => (int)$entry['resolved'],
			'redirected' => (int)$entry['redirected'],
		]);

		$data = array_column($data, null, 'date');

		// Iterate the date strings of all steps (in UTC to not skip
		// or repeat hours when daylight saving time begins or ends)
		$utc  = new DateTimeZone('UTC');
		$date = new DateTimeImmutable($from, $utc);
		$last = new DateTimeImmutable($to . ' 23:59:59', $utc);
		$last = $last->format($use['group_php']);

		// Start months on their first day, as adding
		// a month to e.g. January 31 skips February
		if ($use['group_php'] === 'Y-m') {
			$date = $date->modify('first day of this month');
		}

		$result = [];

		while (($step = $date->format($use['group_php'])) <= $last) {
			$result[] = $data[$step] ?? [
				'date'       => $step,
				'failed'     => 0,
				'resolved'   => 0,
				'redirected' => 0,
			];

			$date = $date->add($use['step']);
		}

		return $result;
	}

	/**
	 * Returns the database table object
	 */
	public function table(): Query
	{
		return $this->database->records();
	}
}
