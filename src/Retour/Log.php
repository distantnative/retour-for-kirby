<?php

namespace Kirby\Retour;

use DateInterval;
use Kirby\Database\Database;
use Kirby\Database\Query;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Date;

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
                COUNT(*) AS hits
            ')
			->where('redirect IS NULL')
			->andWhere('wasResolved IS NULL')
			->andWhere('date > :start', ['start' => $from])
			->andWhere('date < :end', ['end' => $to])
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
	public function flush(string $mode = 'all'): bool
	{
		$table = match ($mode) {
			'all'      => $this->table()->delete(),
			'failures' => $this->table()->delete('redirect IS NULL AND wasResolved IS NULL')
		};

		if ($mode === 'all') {
			$this->database->sqlite_sequence()->delete(['name' => 'records']);
		}

		$vacuum = $this->database->execute('VACUUM');
		return $table && $vacuum;
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
		$limit = $this->retour()->option('deleteAfter', false);

		if ($limit !== false) {
			// Get cutoff date by subtracting limit from today
			$time   = strtotime('-' . $limit . ' month');
			$cutoff = date('Y-m-d 00:00:00', $time);

			/** @var \Kirby\Database\Query $table */
			$table = $this->table()->bindings(['cutoff' => $cutoff]);

			$delete = $table->delete('date < :cutoff');
			$vacuum = $this->database->execute('VACUUM');
			return $delete && $vacuum;
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
			->andWhere('date > :start', ['start' => $from])
			->andWhere('date < :end', ['end' => $to])
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
	 * Returns the Plugin instance
	 */
	public function retour(): Retour
	{
		return $this->retour;
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
	 * @param string $unit timeframe unit (year, month, ...)
	 * @param string $from date sting (yyyy-mm-dd)
	 * @param string $to date sting (yyyy-mm-dd)
	 */
	public function stats(string $unit, string $from, string $to): array
	{
		// Define parts depending on unit
		$use = [
			'group_sql' => '%Y-%m-%d',
			'group_php' => 'Y-m-d',
			'step'      => new DateInterval('P1D')
		];

		switch ($unit) {
			case 'day':
				// Add time to dates to capture full days
				$from .= ' 00:00:00';
				$to   .= ' 23:59:59';

				$use['group_sql'] = '%Y-%m-%d %H';
				$use['group_php'] = 'Y-m-d H';
				$use['step']      = new DateInterval('PT1H');
				break;

			case 'year':
			case 'months':
				$use['group_sql'] = '%Y-%m';
				$use['group_php'] = 'Y-m';
				$use['step']      = new DateInterval('P1M');
				break;
		}

		// Get data from database
		$data = $this->database->query('
            SELECT
                strftime(:group, date) AS date,
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
                strftime(:group, date)
            ORDER BY
                strftime(:group, date)
        ', [
			'from'  => $from,
			'to'    => Date::optional($to)->add($use['step'])->format('Y-m-d'),
			'group' => $use['group_sql']
		], [
			'fetch' => 'array'
		]);

		$data = $data->toArray(fn (array $entry) => [
			'date'       => (string)$entry['date'],
			'failed'     => (int)$entry['failed'],
			'resolved'   => (int)$entry['resolved'],
			'redirected' => (int)$entry['redirected'],
		]);

		$result = [];
		$entry  = array_shift($data);

		for (
			$i = Date::optional($from);
			$i <= Date::optional($to);
			$i->add($use['step'])
		) {
			$step = $i->format($use['group_php']);


			if (($entry['date'] ?? null) === $step) {
				$result[] = $entry;
				$entry    = array_shift($data);
			} else {
				$result[] = [
					'date'       => $step,
					'failed'     => 0,
					'resolved'   => 0,
					'redirected' => 0,
				];
			}
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
