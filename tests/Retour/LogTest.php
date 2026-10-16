<?php

namespace Kirby\Retour;

use Closure;
use Kirby\Database\Database;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClassConstant;

#[CoversClass(Log::class)]
class LogTest extends TestCase
{
	protected function fill(Log $log, int $count, string $time): void
	{
		$date       = date('Y-m-d H:i:s', strtotime($time));
		$connection = $log->database()->connection();
		$insert     = $connection->prepare('INSERT INTO records (date, path) VALUES (?, ?)');

		$connection->beginTransaction();

		for ($i = 0; $i < $count; $i++) {
			$insert->execute([$date, 'path/' . $i . '/' . str_repeat('x', 100)]);
		}

		$connection->commit();
	}

	protected static function latest(): int
	{
		return max(array_keys((new ReflectionClassConstant(Log::class, 'MIGRATIONS'))->getValue()));
	}

	/**
	 * Creates a log database like earlier versions
	 * of the plugin did (full and unused indices)
	 */
	public static function legacy(string $file): void
	{
		Dir::make(dirname($file));

		$connection = new PDO('sqlite:' . $file);
		$connection->exec('CREATE TABLE records (id integer PRIMARY KEY AUTOINCREMENT NOT NULL, date text NOT NULL, path text NOT NULL, referrer text, redirect text, wasResolved integer)');
		$connection->exec('CREATE INDEX idx_redirect_date ON records (redirect, date)');
		$connection->exec('CREATE INDEX idx_path ON records (path)');
		$connection->exec('CREATE INDEX idx_fails ON records (redirect, wasResolved)');
		$connection->exec("INSERT INTO records (date, path, redirect) VALUES ('2025-01-01 10:00:00', 'foo', NULL), ('2025-01-02 10:00:00', 'bar', 'baz')");
	}

	protected function log(
		string|Closure|null $file = null,
		array $options = []
	): Log {
		$file ??= $this->tmp . '/test.sqlite';
		$app    = $this->kirby->clone([
			'options' => array_merge($options, [
				'distantnative.retour.database' => $file
			])
		]);

		$retour = Retour::instance($app);
		return new Log($retour);
	}

	/**
	 * Returns the total and free pages of the database file
	 */
	protected function pages(Log $log): array
	{
		$connection = $log->database()->connection();

		return [
			'total' => (int)$connection->query('PRAGMA page_count')->fetchColumn(),
			'free'  => (int)$connection->query('PRAGMA freelist_count')->fetchColumn()
		];
	}

	/**
	 * Returns the schema version, indices
	 * and index statistics of the log
	 */
	protected function schema(Log $log): array
	{
		$connection = $log->database()->connection();
		$analyzed   = $connection->query("SELECT name FROM sqlite_master WHERE name = 'sqlite_stat1'")->fetchColumn();

		return [
			'version'    => (int)$connection->query('PRAGMA user_version')->fetchColumn(),
			'indices'    => $connection->query("SELECT name, sql FROM sqlite_master WHERE type = 'index' ORDER BY name")->fetchAll(PDO::FETCH_KEY_PAIR),
			'statistics' => $analyzed === false ? [] : $connection->query("SELECT idx, stat FROM sqlite_stat1 WHERE tbl = 'records' ORDER BY idx")->fetchAll(PDO::FETCH_KEY_PAIR)
		];
	}

	public function testAdd(): void
	{
		$log = $this->log();

		// a single statement
		$this->assertTrue($log->add([
			'date'     => $date = '2020-01-01 07:30:00',
			'path'     => $path = 'foo',
			'redirect' => $redirect = 'foo'
		]));
		$this->assertCount(1, $log->database()->trace());

		$log->add([
			'date'     => '2020-01-31 16:30:00',
			'path'     => 'homer/simpson',
			'redirect' => '(:any)/simpson'
		]);

		$results = $log->table()->fetch('array')->all();
		$this->assertSame(2, $results->count());
		$this->assertSame($date, $results->first()['date']);
		$this->assertSame($path, $results->first()['path']);
		$this->assertSame($redirect, $results->first()['redirect']);
	}

	public function testAddTruncatesLongValues(): void
	{
		$log = $this->log();

		$log->add([
			'path'     => str_repeat('a', 5000),
			'referrer' => str_repeat('ä', 5000)
		]);

		$record = $log->table()->fetch('array')->first();
		$this->assertSame(str_repeat('a', 1024), $record['path']);
		$this->assertSame(str_repeat('ä', 1024), $record['referrer']);
	}

	public function testAddTruncatesReferrerHeader(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.database' => $this->tmp . '/test.sqlite'
			],
			'server' => [
				'HTTP_REFERER' => 'https://example.com/' . str_repeat('a', 5000)
			]
		]);

		$log = new Log(Retour::instance($app));
		$log->add(['path' => 'foo']);

		// request without referrer
		$log = $this->log();
		$log->add(['path' => 'bar']);

		$records = $log->table()->fetch('array')->all();
		$this->assertSame(1024, strlen($records->first()['referrer']));
		$this->assertNull($records->last()['referrer']);
	}

	public function testDatabase(): void
	{
		$log = $this->log();
		$this->assertInstanceOf(Database::class, $log->database());
	}

	public function testFails(): void
	{
		$log = $this->log();
		$this->assertSame([], $log->fails('2020-01-01', '2020-12-31'));

		$log->add([
			'date' => '2020-01-01 07:30:00',
			'path' => 'foo'
		]);

		$log->add([
			'date' => $date = '2020-01-31 16:30:00',
			'path' => $path = 'foo'
		]);

		$fail = $log->fails('2020-01-01', '2020-12-31')[0];
		$this->assertSame($path, $fail['path']);
		$this->assertSame($date, $fail['last']);
		$this->assertSame(2, $fail['hits']);
	}

	public function testFailsIncludesBoundaries(): void
	{
		$log = $this->log();

		$log->add(['date' => '2019-12-31 23:59:59', 'path' => 'before']);
		$log->add(['date' => '2020-01-01 00:00:00', 'path' => 'first']);
		$log->add(['date' => '2020-12-31 23:59:59', 'path' => 'last']);
		$log->add(['date' => '2021-01-01 00:00:00', 'path' => 'after']);

		$paths = array_column($log->fails('2020-01-01', '2020-12-31'), 'path');
		sort($paths);
		$this->assertSame(['first', 'last'], $paths);
	}

	public function testFile(): void
	{
		$file = $this->tmp . '/log/test.sqlite';
		$this->assertFalse(F::exists($file));
		$this->log(fn (): string => $file);
		$this->assertTrue(F::exists($file));
	}

	public function testFileDefault(): void
	{
		$log = new Log(Retour::instance());
		$this->assertSame($this->tmp . '/site/logs/retour/log.sqlite', $log->database()->name());
	}

	public function testFirstLast(): void
	{
		$log = $this->log();
		$this->assertSame([], $log->first());
		$this->assertSame([], $log->last());

		$log->add(['path' => 'foo']);

		$this->assertSame(1, $log->table()->fetch('array')->all()->count());
		$this->assertTrue(isset($log->first()['date']));
		$this->assertTrue(isset($log->last()['date']));
		$this->assertSame($log->first(), $log->last());
	}

	public function testFlush(): void
	{
		$log = $this->log();
		$this->assertSame(0, $log->table()->fetch('array')->all()->count());

		$log->add(['path' => 'foo']);
		$this->assertSame(1, $log->table()->fetch('array')->all()->count());

		$this->assertTrue($log->flush());
		$this->assertSame(0, $log->table()->fetch('array')->all()->count());
	}

	public function testFlushFailures(): void
	{
		$log = $this->log();

		// Add a 404 failure and a successful redirect entry
		$log->add(['path' => 'not-found']);
		$log->add(['path' => 'old', 'redirect' => 'old']);
		$this->assertSame(2, $log->table()->fetch('array')->all()->count());

		// Flushing failures should remove only the 404, not the redirect
		$this->assertTrue($log->flush('failures'));
		$this->assertSame(1, $log->table()->fetch('array')->all()->count());

		// The remaining entry must be the redirect, not the failure
		$remaining = $log->table()->fetch('array')->all()->first();
		$this->assertSame('old', $remaining['redirect']);
	}

	public function testFlushInvalidMode(): void
	{
		$log = $this->log();
		$log->add(['path' => 'foo']);

		try {
			$log->flush('foo');
			$this->fail('Unknown modes must be rejected');
		} catch (InvalidArgumentException $e) {
			$this->assertSame('Invalid flush mode: foo', $e->getMessage());
			$this->assertSame(1, $log->table()->fetch('array')->all()->count());
			$this->assertNotContains('VACUUM', array_column($log->database()->trace(), 'query'));
		}
	}

	public function testIndices(): void
	{
		$log     = $this->log();
		$indices = $log->database()->query(
			'SELECT name FROM sqlite_master WHERE type = :type ORDER BY name',
			['type' => 'index'],
			['fetch' => 'array']
		);

		$this->assertSame(
			['idx_date', 'idx_redirect_date'],
			array_column($indices->toArray(), 'name')
		);
	}

	public function testMigrateLegacyLog(): void
	{
		static::legacy($file = $this->tmp . '/test.sqlite');

		// existing logs are migrated when opening the Panel view
		$log = $this->log($file);
		$this->assertSame(0, $this->schema($log)['version']);
		$this->assertTrue($log->migrate());

		$schema = $this->schema($log);
		$this->assertSame(static::latest(), $schema['version']);
		$this->assertSame(['idx_date', 'idx_redirect_date'], array_keys($schema['indices']));
		$this->assertStringEndsWith('WHERE redirect IS NOT NULL', $schema['indices']['idx_redirect_date']);
		$this->assertSame(['idx_date' => '1000000 1', 'idx_redirect_date' => '400000 8000 1'], $schema['statistics']);

		// records are kept
		$this->assertSame(['bar', 'foo'], $log->table()->select('path')->order('path ASC')->fetch('array')->all()->pluck('path'));

		// nothing left to do
		$this->assertTrue($log->migrate());
		$this->assertSame($schema, $this->schema($log));
	}

	public function testMigrateNewLog(): void
	{
		$log    = $this->log();
		$schema = $this->schema($log);

		$this->assertSame(static::latest(), $schema['version']);
		$this->assertSame(['idx_date', 'idx_redirect_date'], array_keys($schema['indices']));
		$this->assertStringEndsWith('WHERE redirect IS NOT NULL', $schema['indices']['idx_redirect_date']);
		$this->assertSame(['idx_date' => '1000000 1', 'idx_redirect_date' => '400000 8000 1'], $schema['statistics']);
	}

	public function testMigrateRollsBackFailedMigration(): void
	{
		$retour = Retour::instance($this->app([
			'distantnative.retour.database' => $this->tmp . '/test.sqlite'
		]));

		$log = new class ($retour) extends Log {
			protected const MIGRATIONS = parent::MIGRATIONS + [
				99 => [
					'CREATE INDEX idx_test ON records (path)',
					'NOT VALID SQL'
				]
			];
		};

		// the earlier migrations are applied, the failed one rolled back
		$schema = $this->schema($log);
		$this->assertSame(static::latest(), $schema['version']);
		$this->assertArrayNotHasKey('idx_test', $schema['indices']);
		$this->assertFalse($log->migrate());
	}

	public function testMigrations(): void
	{
		// versions must be consecutive, starting with 1
		$versions = array_keys((new ReflectionClassConstant(Log::class, 'MIGRATIONS'))->getValue());
		$this->assertSame(range(1, count($versions)), $versions);
	}

	public function testPurge(): void
	{
		$file = $this->tmp . '/test.sqlite';
		$log  = $this->log($file, [
			'distantnative.retour.deleteAfter' => 6
		]);

		$this->assertSame(0, $log->table()->fetch('array')->all()->count());

		$log->add([
			'date' => date('Y-m-d H:i:s', strtotime('-12 month')),
			'path' => 'foo'
		]);

		$log->add([
			'date' => date('Y-m-d H:i:s', strtotime('-3 month')),
			'path' => 'foo'
		]);

		$this->assertSame(2, $log->table()->fetch('array')->all()->count());

		$this->assertTrue($log->purge());
		$this->assertSame(1, $log->table()->fetch('array')->all()->count());
	}

	public function testPurgeDactivated(): void
	{
		$log = $this->log();
		$this->assertTrue($log->purge());
	}

	public function testPurgeKeepsInsignificantFreeSpace(): void
	{
		$log = $this->log(null, ['distantnative.retour.deleteAfter' => 6]);
		$this->fill($log, 100, '-12 month');
		$this->fill($log, 2000, '-1 month');
		$before = $this->pages($log);

		$this->assertTrue($log->purge());

		// new records reuse the free pages
		$after = $this->pages($log);
		$this->assertGreaterThan(0, $after['free']);
		$this->assertSame($before['total'], $after['total']);
		$this->assertNotContains('VACUUM', array_column($log->database()->trace(), 'query'));
	}

	public function testPurgeReclaimsSignificantFreeSpace(): void
	{
		$log = $this->log(null, ['distantnative.retour.deleteAfter' => 6]);
		$this->fill($log, 2000, '-12 month');
		$this->fill($log, 500, '-1 month');
		$before = $this->pages($log);

		$this->assertTrue($log->purge());

		$after = $this->pages($log);
		$this->assertSame(0, $after['free']);
		$this->assertLessThan($before['total'] / 2, $after['total']);
	}

	public function testPurgeWithoutOutdatedRecords(): void
	{
		$log = $this->log(options: [
			'distantnative.retour.deleteAfter' => 6
		]);

		$log->add([
			'date' => date('Y-m-d H:i:s', strtotime('-3 month')),
			'path' => 'foo'
		]);

		$this->assertTrue($log->purge());
		$this->assertSame(1, $log->table()->fetch('array')->all()->count());
		$this->assertNotContains('VACUUM', array_column($log->database()->trace(), 'query'));
	}

	public function testRedirects(): void
	{
		$log = $this->log();
		$this->assertSame([], $log->redirects('2020-01-01', '2020-12-31'));

		$log->add(['date' => '2020-01-01 07:30:00', 'path' => 'foo', 'redirect' => 'foo']);
		$log->add(['date' => $last = '2020-01-31 16:30:00', 'path' => 'foo', 'redirect' => 'foo']);
		$log->add(['date' => '2020-02-01 10:00:00', 'path' => 'homer/simpson', 'redirect' => '(:any)/simpson']);
		$log->add(['date' => '2020-01-31 16:30:00', 'path' => 'foo']);
		$log->add(['date' => '2021-01-31 16:30:00', 'path' => 'foo', 'redirect' => 'foo']);

		$this->assertSame([
			'(:any)/simpson' => ['hits' => 1, 'last' => '2020-02-01 10:00:00'],
			'foo'            => ['hits' => 2, 'last' => $last],
		], $log->redirects('2020-01-01', '2020-12-31'));
	}

	public function testRemove(): void
	{
		$log = $this->log();
		$this->assertTrue($log->remove('none'));

		$log->add(['path' => 'foo']);
		$log->add(['path' => 'foo']);
		$log->add(['path' => 'foo', 'referrer' => 'bar']);
		$log->add(['path' => 'foo', 'referrer' => 'homer simpson']);
		$log->add(['path' => 'bar']);

		$this->assertSame(5, $log->table()->fetch('array')->all()->count());
		$this->assertTrue($log->remove('foo'));
		$this->assertSame(1, $log->table()->fetch('array')->all()->count());
		$this->assertTrue($log->remove('bar'));
		$this->assertSame(0, $log->table()->fetch('array')->all()->count());
	}

	public function testRemoveFailures(): void
	{
		$log = $this->log();
		$this->assertTrue($log->removeFailures(fn () => true));

		$log->add(['path' => 'wp-admin/setup.php']);
		$log->add(['path' => 'wp-admin/setup.php']);
		$log->add(['path' => 'wp-login.php']);
		$log->add(['path' => 'foo']);
		$log->add(['path' => 'wp-old', 'redirect' => 'wp-old']);

		$this->assertTrue($log->removeFailures(
			fn (string $path) => str_starts_with($path, 'wp-')
		));

		// keeps other failures and hits of redirects
		$paths = $log->table()->select('path')->fetch('array')->all()->toArray(
			fn (array $record) => $record['path']
		);
		sort($paths);
		$this->assertSame(['foo', 'wp-old'], $paths);
	}

	public function testRemoveFailuresMany(): void
	{
		$log = $this->log();
		$db  = $log->database()->connection();

		// more paths than SQLite allows bound parameters
		$db->beginTransaction();

		for ($i = 0; $i < 2000; $i++) {
			$log->add(['path' => 'foo-' . $i]);
		}

		$db->commit();
		$log->add(['path' => 'bar']);

		$this->assertTrue($log->removeFailures(
			fn (string $path) => str_starts_with($path, 'foo-')
		));

		$this->assertSame(['bar'], array_column($log->fails('2000-01-01', '2099-12-31'), 'path'));
	}

	public function testResolve(): void
	{
		$log = $this->log();
		$log->add([
			'date' => '2020-01-01 07:30:00',
			'path' => 'foo'
		]);

		$log->add([
			'date' => '2020-01-31 16:30:00',
			'path' => 'bar'
		]);

		$this->assertSame(2, count($log->fails('2020-01-01', '2020-12-31')));
		$this->assertTrue($log->resolve('foo'));
		$this->assertSame(1, count($log->fails('2020-01-01', '2020-12-31')));
	}

	public function testStats(): void
	{
		$file = __DIR__ . '/fixtures/sample.sqlite';
		$log  = $this->log($file);

		// Year
		$stats = $log->stats('year', '2019-01-01', '2019-12-31');
		$this->assertSame(12, count($stats));
		$this->assertSame('2019-12', $stats[11]['date']);
		$this->assertSame(235, $stats[11]['failed']);
		$this->assertSame(68, $stats[11]['resolved']);
		$this->assertSame(1021, $stats[11]['redirected']);

		// Month
		$stats = $log->stats('month', '2019-11-01', '2019-11-30');
		$this->assertSame(30, count($stats));
		$this->assertSame('2019-11-30', $stats[29]['date']);
		$this->assertSame(16, $stats[29]['failed']);
		$this->assertSame(3, $stats[29]['resolved']);
		$this->assertSame(33, $stats[29]['redirected']);

		// Week
		$stats = $log->stats('week', '2019-11-04', '2019-11-10');
		$this->assertSame(7, count($stats));
		$this->assertSame('2019-11-10', $stats[6]['date']);
		$this->assertSame(14, $stats[6]['failed']);
		$this->assertSame(3, $stats[6]['resolved']);
		$this->assertSame(30, $stats[6]['redirected']);

		// Day
		$stats = $log->stats('day', '2019-11-04', '2019-11-04');
		$this->assertSame(24, count($stats));
		$this->assertSame('2019-11-04 06', $stats[6]['date']);
		$this->assertSame(0, $stats[6]['failed']);
		$this->assertSame(0, $stats[6]['resolved']);
		$this->assertSame(4, $stats[6]['redirected']);

	}

	public function testStatsDaylightSavingTime(): void
	{
		$timezone = date_default_timezone_get();
		date_default_timezone_set('Europe/Berlin');

		try {
			$log = $this->log();

			// clocks go forward
			$stats = $log->stats('day', '2024-03-31', '2024-03-31');
			$this->assertCount(24, $stats);
			$this->assertSame('2024-03-31 02', $stats[2]['date']);

			// clocks go back
			$stats = $log->stats('day', '2024-10-27', '2024-10-27');
			$this->assertCount(24, $stats);
			$this->assertSame('2024-10-27 23', $stats[23]['date']);
		} finally {
			date_default_timezone_set($timezone);
		}
	}

	public function testStatsMonths(): void
	{
		$log = $this->log();

		$log->add(['date' => '2024-01-30 23:59:59', 'path' => 'before']);
		$log->add(['date' => '2024-01-31 00:00:00', 'path' => 'jan']);
		$log->add(['date' => '2024-02-15 10:00:00', 'path' => 'feb']);
		$log->add(['date' => '2024-03-10 10:00:00', 'path' => 'mar']);
		$log->add(['date' => '2024-03-11 10:00:00', 'path' => 'mar', 'redirect' => 'mar']);
		$log->add(['date' => '2024-04-01 00:00:00', 'path' => 'apr']);
		$log->add(['date' => '2024-04-25 23:59:59', 'path' => 'apr']);
		$log->add(['date' => '2024-04-26 00:00:00', 'path' => 'after']);

		$stats = $log->stats('months', '2024-01-31', '2024-04-25');

		$this->assertSame(['2024-01', '2024-02', '2024-03', '2024-04'], array_column($stats, 'date'));
		$this->assertSame([1, 1, 1, 2], array_column($stats, 'failed'));
		$this->assertSame([0, 0, 1, 0], array_column($stats, 'redirected'));

		// same total as the failures table
		$fails = $log->fails('2024-01-31', '2024-04-25');
		$this->assertSame(array_sum(array_column($fails, 'hits')), array_sum(array_column($stats, 'failed')));
	}

	public function testStatsUpperBound(): void
	{
		$log = $this->log();

		$log->add(['date' => '2024-01-10 23:59:59', 'path' => 'last']);
		$log->add(['date' => '2024-01-11 00:00:00', 'path' => 'after']);
		$log->add(['date' => '2024-02-01 00:00:00', 'path' => 'after']);

		foreach (['days', 'month', 'months'] as $unit) {
			$stats = $log->stats($unit, '2024-01-01', '2024-01-10');
			$this->assertSame(1, array_sum(array_column($stats, 'failed')), $unit);
		}
	}
}
