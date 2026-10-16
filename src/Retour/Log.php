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
use Kirby\Retour\Log\Migrations;
use Kirby\Toolkit\Str;
use PDO;
use Throwable;

/**
 * Reads and writes to the database log
 * for all 404 requests or successful redirects
 */
class Log
{
	/**
	 * Maximum length of logged paths and referrers,
	 * as bots can request arbitrarily long URLs
	 */
	protected const MAX_LENGTH = 1024;

	protected Database $database;

	public function __construct(
		protected Retour $retour
	) {
		$file = $retour->option('database');

		if ($file instanceof Closure) {
			$file = $file();
		}

		$new = F::exists($file) === false;

		if ($new === true) {
			Dir::make(dirname($file));
		}

		$this->database = new Database([
			'type'     => 'sqlite',
			'database' => $file
		]);

		if ($new === true) {
			$this->migrate();
		}
	}

	/**
	 * Returns whether the record has been stored
	 */
	public function add(array $props): bool
	{
		$referrer = $props['referrer'] ?? $this->retour->kirby()->request()->header('Referer');

		if ($referrer !== null) {
			$referrer = Str::substr($referrer, 0, static::MAX_LENGTH);
		}

		// unlike the query builder, a plain statement doesn't need
		// to look up the table schema first (on every request)
		return $this->database->execute(
			'INSERT INTO records (date, path, referrer, redirect) VALUES (:date, :path, :referrer, :redirect)',
			[
				'date'     => $props['date'] ?? date('Y-m-d H:i:s'),
				'path'     => Str::substr($props['path'], 0, static::MAX_LENGTH),
				'referrer' => $referrer,
				'redirect' => $props['redirect'] ?? null
			]
		);
	}

	/**
	 * Returns a query for the records of full days from `$from` to `$to`
	 *
	 * @param $from date string (yyyy-mm-dd)
	 * @param $to date string (yyyy-mm-dd)
	 */
	protected function between(string $from, string $to): Query
	{
		return $this->table()
			->where('date >= :start', ['start' => $from . ' 00:00:00'])
			->andWhere('date <= :end', ['end' => $to . ' 23:59:59']);
	}

	/**
	 * @since 5.6.0
	 */
	public function database(): Database
	{
		return $this->database;
	}

	/**
	 * Returns unresolved failures grouped by path and referrer
	 *
	 * @param $from date string (yyyy-mm-dd)
	 * @param $to date string (yyyy-mm-dd)
	 */
	public function fails(string $from, string $to): array
	{
		$fails = $this->between($from, $to)
			->select('
                path,
                referrer,
                MAX(date) AS last,
                COUNT(*) AS hits
            ')
			->andWhere('redirect IS NULL')
			->andWhere('wasResolved IS NULL')
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

	public function first(): array
	{
		return $this->single('date ASC');
	}

	/**
	 * Deletes all records (and resets the ids) or only unresolved failures
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

		return $deleted && $this->reclaim();
	}

	public function last(): array
	{
		return $this->single('date DESC');
	}

	/**
	 * Creates the log database or brings
	 * one of an earlier version up to date
	 */
	public function migrate(): bool
	{
		return (new Migrations($this))->run();
	}

	/**
	 * Deletes entries older than the `deleteAfter` option (in months)
	 */
	public function purge(): bool
	{
		$limit = $this->retour->option('deleteAfter');

		if ($limit !== false) {
			$time = strtotime('-' . $limit . ' month');

			if ($time === false) {
				return false;
			}

			$cutoff = date('Y-m-d 00:00:00', $time);

			/** @var Query $table */
			$table = $this->table()->bindings(['cutoff' => $cutoff]);

			return $table->delete('date < :cutoff') && $this->reclaim();
		}

		return true;
	}

	/**
	 * Gives free space back to the file system by rebuilding the
	 * database file, but only if it's a significant part of the file;
	 * otherwise new records reuse the free space and the rebuild
	 * (which locks the log and takes long for large logs) isn't worth it
	 */
	public function reclaim(): bool
	{
		$connection = $this->database->connection();
		$pages      = (int)$connection->query('PRAGMA page_count')->fetchColumn();
		$free       = (int)$connection->query('PRAGMA freelist_count')->fetchColumn();

		// less than a fifth of the file is free
		if ($free === 0 || $free < $pages / 5) {
			return true;
		}

		return $this->database->execute('VACUUM');
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
		$data = $this->between($from, $to)
			->select('
                redirect,
                COUNT(*) AS hits,
                MAX(date) AS last
            ')
			->andWhere('redirect IS NOT NULL')
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
	 * Deletes all records of the path
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

		$paths = array_filter($paths, $filter);

		if ($paths === []) {
			return true;
		}

		// collect the paths in a temporary table to delete
		// their records in a single pass over the log
		$db = $this->database->connection();
		$db->beginTransaction();

		try {
			$db->exec('CREATE TEMP TABLE retour_remove (path TEXT PRIMARY KEY)');
			$insert = $db->prepare('INSERT INTO temp.retour_remove (path) VALUES (?)');

			foreach ($paths as $path) {
				$insert->execute([$path]);
			}

			$db->exec('DELETE FROM records WHERE redirect IS NULL AND path IN (SELECT path FROM temp.retour_remove)');
			$db->exec('DROP TABLE temp.retour_remove');
			$db->commit();
		} catch (Throwable) {
			$db->rollBack();
			return false;
		}

		return true;
	}

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
	 * Counts redirected, resolved and failed requests
	 * per hour, day or month of the timespan
	 *
	 * @param $unit `day`, `days`, `month`, `months` or `year`
	 * @param $from date string (yyyy-mm-dd)
	 * @param $to date string (yyyy-mm-dd)
	 */
	public function stats(string $unit, string $from, string $to): array
	{
		[$format, $step] = match ($unit) {
			'day'            => ['Y-m-d H', new DateInterval('PT1H')],
			'year', 'months' => ['Y-m', new DateInterval('P1M')],
			default          => ['Y-m-d', new DateInterval('P1D')]
		};

		// counting each step with a range query on the date index
		// is faster than grouping all records by a part of their date
		$query = $this->database->connection()->prepare('
			SELECT
				COUNT(redirect) AS redirected,
				SUM(redirect IS NULL AND wasResolved IS NOT NULL) AS resolved,
				SUM(redirect IS NULL AND wasResolved IS NULL) AS failed
			FROM records
			WHERE date >= :start AND date < :end
		');

		// Iterate all steps (in UTC to not skip or repeat
		// hours when daylight saving time begins or ends)
		$utc  = new DateTimeZone('UTC');
		$date = new DateTimeImmutable($from, $utc);
		$last = new DateTimeImmutable($to . ' 23:59:59', $utc);
		$min  = $date->format('Y-m-d H:i:s');
		$max  = $last->modify('+1 second')->format('Y-m-d H:i:s');
		$last = $last->format($format);

		// Start months on their first day, as adding
		// a month to e.g. January 31 skips February
		if ($format === 'Y-m') {
			$date = $date->modify('first day of this month');
		}

		$result = [];

		while (($label = $date->format($format)) <= $last) {
			$next = $date->add($step);

			// the first and last step only cover the timespan
			$query->execute([
				'start' => max($date->format('Y-m-d H:i:s'), $min),
				'end'   => min($next->format('Y-m-d H:i:s'), $max)
			]);

			$counts = $query->fetch(PDO::FETCH_ASSOC);

			$result[] = [
				'date'       => $label,
				'failed'     => (int)$counts['failed'],
				'resolved'   => (int)$counts['resolved'],
				'redirected' => (int)$counts['redirected'],
			];

			$date = $next;
		}

		return $result;
	}

	public function table(): Query
	{
		return $this->database->records();
	}
}
