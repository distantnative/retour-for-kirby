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
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Str;
use PDO;
use Throwable;

/**
 * Stores failures and redirect hits with integer dates (see `timestamp()`);
 * `$from` and `$to` params are `yyyy-mm-dd`
 */
class Log
{
	/**
	 * For paths and referrers, as bots can request arbitrarily long URLs
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
	 * Stores a failure (`path`, `referrer`) or a `redirect` hit
	 */
	public function add(array $props): bool
	{
		$redirect = $props['redirect'] ?? null;
		$path     = null;
		$referrer = null;

		// redirect hits don't need their path and referrer
		if ($redirect === null) {
			$path     = Str::substr($props['path'], 0, static::MAX_LENGTH);
			$referrer = $props['referrer'] ?? $this->retour->kirby()->request()->header('Referer');

			if ($referrer !== null) {
				$referrer = Str::substr($referrer, 0, static::MAX_LENGTH);
			}
		}

		// a plain statement skips the query builder's schema lookup on every request
		$added = $this->database->execute(
			'INSERT INTO records (date, path, referrer, redirect) VALUES (:date, :path, :referrer, :redirect)',
			[
				'date'     => static::timestamp($props['date'] ?? date('Y-m-d H:i:s')),
				'path'     => $path,
				'referrer' => $referrer,
				'redirect' => $redirect
			]
		);

		// every 1,000th record purges up to 10,000 outdated ones (< 0.1 s),
		// so the log doesn't grow even if nobody opens the Panel
		if ($added === true && $this->database->lastId() % 1000 === 0) {
			$this->purge(limit: 10000);
		}

		return $added;
	}

	protected function between(string $from, string $to): Query
	{
		return $this->table()
			->where('date >= :start', ['start' => static::timestamp($from)])
			->andWhere('date <= :end', ['end' => static::timestamp($to . ' 23:59:59')]);
	}

	/**
	 * @since 5.6.0
	 */
	public function database(): Database
	{
		return $this->database;
	}

	/**
	 * Unresolved failures, grouped by path and referrer
	 *
	 * @param $sortBy `path`, `referrer`, `last` or `hits` with `asc` or `desc`
	 * @throws InvalidArgumentException If the sorting is invalid
	 */
	public function fails(
		string $from,
		string $to,
		string|null $sortBy = null,
		string|null $searchterm = null,
		int $page = 1,
		int $limit = 50
	): Collection {
		$sortBy ??= 'hits desc';

		[$column, $direction] = Str::split(strtolower($sortBy), ' ') + [1 => 'asc'];

		if (
			in_array($column, ['path', 'referrer', 'last', 'hits'], true) === false ||
			in_array($direction, ['asc', 'desc'], true) === false
		) {
			throw new InvalidArgumentException(
				message: 'Invalid sorting: ' . $sortBy
			);
		}

		// idx_date is faster for up to a month; for longer timespans, `+date`
		// keeps SQLite on idx_failures, which has the failures grouped already
		$days = (new DateTimeImmutable($from))->diff(new DateTimeImmutable($to))->days;
		$date = $days < 31 ? 'date' : '+date';

		$query = $this->table()
			->select('
                path,
                referrer,
                datetime(MAX(date), \'unixepoch\') AS last,
                COUNT(*) AS hits
            ')
			->where($date . ' >= :start', ['start' => static::timestamp($from)])
			->andWhere($date . ' <= :end', ['end' => static::timestamp($to . ' 23:59:59')])
			->andWhere('redirect IS NULL')
			->andWhere('wasResolved IS NULL')
			->group('path, referrer')
			// tie-breakers keep pages stable, as many failures share their hits;
			// sorted by path, one direction for both lets SQLite read idx_failures in order
			->order(match ($column) {
				'path'  => 'path ' . $direction . ', referrer ' . $direction,
				default => $column . ' ' . $direction . ', path, referrer'
			})
			->fetch('array');

		if ($searchterm !== null) {
			$query->andWhere(
				"(path LIKE :searchterm ESCAPE '\\' OR referrer LIKE :searchterm ESCAPE '\\')",
				['searchterm' => '%' . addcslashes($searchterm, '%_\\') . '%']
			);
		}

		return $query->page($page, $limit)->map(fn (array $fail): array => [
			...$fail,
			'hits' => (int)$fail['hits']
		]);
	}

	public function first(): array
	{
		return $this->single('date ASC');
	}

	/**
	 * @param $mode `all` (also resets the ids) or `failures` (unresolved only)
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

	public function migrate(): bool
	{
		return (new Migrations($this))->run();
	}

	/**
	 * Deletes records older than `deleteAfter` months; with a limit, only
	 * that many and without rebuilding the file, e.g. while logging
	 */
	public function purge(int|null $limit = null): bool
	{
		$months = $this->retour->option('deleteAfter');

		if ($months === false) {
			return true;
		}

		$time = strtotime('-' . $months . ' month');

		if ($time === false) {
			return false;
		}

		$cutoff = static::timestamp(date('Y-m-d', $time));

		if ($limit !== null) {
			return $this->database->execute(
				'DELETE FROM records WHERE id IN (SELECT id FROM records WHERE date < :cutoff LIMIT :limit)',
				['cutoff' => $cutoff, 'limit' => $limit]
			);
		}

		/** @var Query $table */
		$table = $this->table()->bindings(['cutoff' => $cutoff]);

		return $table->delete('date < :cutoff') && $this->reclaim();
	}

	/**
	 * Rebuilds the file only if a fifth is free, otherwise new records
	 * reuse the space and the slow, locking rebuild isn't worth it
	 */
	public function reclaim(): bool
	{
		$connection = $this->database->connection();
		$pages      = (int)$connection->query('PRAGMA page_count')->fetchColumn();
		$free       = (int)$connection->query('PRAGMA freelist_count')->fetchColumn();

		if ($free === 0 || $free < $pages / 5) {
			return true;
		}

		return $this->database->execute('VACUUM');
	}

	/**
	 * Redirects without hits are missing
	 *
	 * @return array<string, array{hits: int, last: string}>
	 */
	public function redirects(string $from, string $to): array
	{
		$data = $this->between($from, $to)
			->select('
                redirect,
                COUNT(*) AS hits,
                datetime(MAX(date), \'unixepoch\') AS last
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
	 * Deletes only failures, redirect hits stay for the stats
	 */
	public function remove(string $path): bool
	{
		return $this->table()
			->where(['path' => $path])
			->andWhere('redirect IS NULL')
			->delete();
	}

	/**
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

		// a temporary table lets one pass over the log delete all paths
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
		return $this->table()
			->where(['path' => $path])
			->andWhere('redirect IS NULL')
			->update(['wasResolved' => 1]);
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

		return ['date' => gmdate('Y-m-d H:i:s', (int)$result['date'])];
	}

	/**
	 * @param $unit `day`, `days`, `week`, `month`, `months` or `year`
	 */
	public function stats(string $unit, string $from, string $to): array
	{
		[$format, $step] = match ($unit) {
			'day'            => ['Y-m-d H', new DateInterval('PT1H')],
			'year', 'months' => ['Y-m', new DateInterval('P1M')],
			default          => ['Y-m-d', new DateInterval('P1D')]
		};

		// a range query per step beats grouping all records by a date part
		$query = $this->database->connection()->prepare('
			SELECT
				COUNT(redirect) AS redirected,
				SUM(redirect IS NULL AND wasResolved IS NOT NULL) AS resolved,
				SUM(redirect IS NULL AND wasResolved IS NULL) AS failed
			FROM records
			WHERE date >= :start AND date < :end
		');

		// UTC, to not skip or repeat hours when daylight saving time changes
		$utc  = new DateTimeZone('UTC');
		$date = new DateTimeImmutable($from, $utc);
		$last = new DateTimeImmutable($to . ' 23:59:59', $utc);
		$min  = $date->getTimestamp();
		$max  = $last->getTimestamp() + 1;
		$last = $last->format($format);

		// adding a month to e.g. January 31 would skip February
		if ($format === 'Y-m') {
			$date = $date->modify('first day of this month');
		}

		$result = [];

		while (($label = $date->format($format)) <= $last) {
			$next = $date->add($step);

			// the first and last step only cover the timespan
			$query->execute([
				'start' => max($date->getTimestamp(), $min),
				'end'   => min($next->getTimestamp(), $max)
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

	/**
	 * Seconds since 1970 of the local time as if it was UTC, which keeps
	 * the local time like the text of earlier versions in less space
	 */
	public static function timestamp(string $date): int
	{
		return (new DateTimeImmutable($date, new DateTimeZone('UTC')))->getTimestamp();
	}
}
