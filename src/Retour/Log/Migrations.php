<?php

namespace Kirby\Retour\Log;

use Kirby\Retour\Log;
use PDO;
use Throwable;

/**
 * Creates the log database and brings logs of earlier
 * versions up to date, tracked by `PRAGMA user_version`
 */
class Migrations
{
	/**
	 * Index statistics of a large log, so that the query planner
	 * picks the right indices before the log has grown
	 */
	protected const STATISTICS = [
		'idx_date'          => '1000000 1',
		'idx_redirect_date' => '400000 8000 1'
	];

	/**
	 * Schema changes by database version, applied
	 * in order to new logs and logs of earlier versions
	 */
	protected const VERSIONS = [
		1 => [
			'CREATE TABLE IF NOT EXISTS records (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, date TEXT NOT NULL, path TEXT NOT NULL, referrer TEXT, redirect TEXT, wasResolved INTEGER)',
			'CREATE INDEX IF NOT EXISTS idx_date ON records (date)',
			// only for redirect hits, failures don't need it
			'DROP INDEX IF EXISTS idx_redirect_date',
			'CREATE INDEX idx_redirect_date ON records (redirect, date) WHERE redirect IS NOT NULL',
			// unused indices of earlier versions
			'DROP INDEX IF EXISTS idx_fails',
			'DROP INDEX IF EXISTS idx_path',
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

	/**
	 * Returns the schema changes of the versions not applied yet
	 */
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
	 * Applies all pending versions, each in its own transaction;
	 * a failed one is rolled back and tried again the next time
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

		// load the new statistics and give the
		// space of dropped indices back if significant
		$connection->exec('ANALYZE sqlite_schema');
		$this->log->reclaim();

		return true;
	}

	/**
	 * Replaces the index statistics with the ones of a large log
	 */
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

	/**
	 * Returns the version of the log database
	 */
	public function version(): int
	{
		return (int)$this->connection()->query('PRAGMA user_version')->fetchColumn();
	}
}
