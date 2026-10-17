<?php

namespace Kirby\Retour\Log;

use Kirby\Retour\Log;
use PDO;
use Throwable;

/**
 * Creates and upgrades the log database, tracked by `PRAGMA user_version`
 */
class Migrations
{
	/**
	 * Stats of a large log, so SQLite picks the right indices from the start
	 */
	protected const STATISTICS = [
		'idx_date'          => '1000000 1',
		'idx_redirect_date' => '400000 8000 1'
	];

	/**
	 * Applied in order to new logs and logs of earlier versions
	 */
	protected const VERSIONS = [
		1 => [
			// the text-date table of earlier versions, so new logs share the next steps
			'CREATE TABLE IF NOT EXISTS records (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date TEXT NOT NULL, path TEXT NOT NULL, referrer TEXT, redirect TEXT, wasResolved INTEGER)',
			// integer dates (see `Log::timestamp()`), redirect hits without path and
			// referrer; a new table, as columns can't change type or constraints
			'CREATE TABLE records_new (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date INTEGER NOT NULL, path TEXT, referrer TEXT, redirect TEXT, wasResolved INTEGER)',
			"INSERT INTO records_new SELECT id, CAST(strftime('%s', date) AS INTEGER), CASE WHEN redirect IS NULL THEN path END, CASE WHEN redirect IS NULL THEN referrer END, redirect, wasResolved FROM records",
			// also drops all indices of earlier versions
			'DROP TABLE records',
			'ALTER TABLE records_new RENAME TO records',
			'CREATE INDEX idx_date ON records (date)',
			'CREATE INDEX idx_redirect_date ON records (redirect, date) WHERE redirect IS NOT NULL',
			// groups the failures and finds them by path
			'CREATE INDEX idx_failures ON records (path, referrer, date, wasResolved) WHERE redirect IS NULL',
		]
	];

	public function __construct(
		protected Log $log
	) {
	}

	protected function connection(): PDO
	{
		return $this->log->database()->connection();
	}

	public function pending(): array
	{
		$current = $this->version();

		return array_filter(
			static::VERSIONS,
			fn (int $version) => $version > $current,
			ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * Each version in its own transaction; a failed one is retried next time
	 */
	public function run(): bool
	{
		$pending = $this->pending();

		if ($pending === []) {
			return true;
		}

		$connection = $this->connection();

		foreach ($pending as $version => $statements) {
			$connection->beginTransaction();

			try {
				foreach ($statements as $statement) {
					$connection->exec($statement);
				}

				$this->statistics();
				$connection->exec('PRAGMA user_version = ' . $version);
				$connection->commit();
			} catch (Throwable) {
				$connection->rollBack();
				return false;
			}
		}

		// load the new statistics and reclaim the space of dropped indices
		$connection->exec('ANALYZE sqlite_schema');
		$this->log->reclaim();

		return true;
	}

	protected function statistics(): void
	{
		$connection = $this->connection();

		// creates the statistics table without analyzing the records
		$connection->exec('ANALYZE sqlite_schema');
		$connection->exec("DELETE FROM sqlite_stat1 WHERE tbl = 'records'");

		$insert = $connection->prepare("INSERT INTO sqlite_stat1 (tbl, idx, stat) VALUES ('records', ?, ?)");

		foreach (static::STATISTICS as $index => $stat) {
			$insert->execute([$index, $stat]);
		}
	}

	public function version(): int
	{
		return (int)$this->connection()->query('PRAGMA user_version')->fetchColumn();
	}
}
