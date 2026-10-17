<?php

namespace Kirby\Retour\Log;

use Kirby\Filesystem\Dir;
use Kirby\Retour\Log;
use Kirby\Retour\Retour;
use Kirby\Retour\TestCase;
use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClassConstant;

#[CoversClass(Migrations::class)]
class MigrationsTest extends TestCase
{
	protected static function latest(): int
	{
		return max(array_keys(static::versions()));
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

	protected function log(): Log
	{
		$retour = Retour::instance($this->app([
			'distantnative.retour.database' => $this->tmp . '/test.sqlite'
		]));

		return new Log($retour);
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

	public function testRunLegacyLog(): void
	{
		static::legacy($this->tmp . '/test.sqlite');

		// existing logs are not migrated when opened
		$log        = $this->log();
		$migrations = new Migrations($log);
		$this->assertSame(0, $migrations->version());
		$this->assertSame(array_keys(static::versions()), array_keys($migrations->pending()));

		$this->assertTrue($migrations->run());
		$this->assertSame(static::latest(), $migrations->version());
		$this->assertSame([], $migrations->pending());

		$schema = $this->schema($log);
		$this->assertSame(['idx_date', 'idx_failures', 'idx_redirect_date'], array_keys($schema['indices']));
		$this->assertStringEndsWith('WHERE redirect IS NULL', $schema['indices']['idx_failures']);
		$this->assertStringEndsWith('WHERE redirect IS NOT NULL', $schema['indices']['idx_redirect_date']);
		$this->assertSame(['idx_date' => '1000000 1', 'idx_redirect_date' => '400000 8000 1'], $schema['statistics']);

		// records are kept
		$this->assertSame(['bar', 'foo'], $log->table()->select('path')->order('path ASC')->fetch('array')->all()->pluck('path'));

		// nothing left to do
		$this->assertTrue($migrations->run());
		$this->assertSame($schema, $this->schema($log));
	}

	public function testRunNewLog(): void
	{
		// new logs are migrated when created
		$log    = $this->log();
		$schema = $this->schema($log);

		$this->assertSame(static::latest(), $schema['version']);
		$this->assertSame(['idx_date', 'idx_failures', 'idx_redirect_date'], array_keys($schema['indices']));
		$this->assertStringEndsWith('WHERE redirect IS NULL', $schema['indices']['idx_failures']);
		$this->assertStringEndsWith('WHERE redirect IS NOT NULL', $schema['indices']['idx_redirect_date']);
		$this->assertSame(['idx_date' => '1000000 1', 'idx_redirect_date' => '400000 8000 1'], $schema['statistics']);
	}

	public function testRunRollsBackFailedVersion(): void
	{
		$log        = $this->log();
		$migrations = new class ($log) extends Migrations {
			protected const VERSIONS = parent::VERSIONS + [
				99 => [
					'CREATE INDEX idx_test ON records (path)',
					'NOT VALID SQL'
				]
			];
		};

		$this->assertFalse($migrations->run());

		// the failed version is rolled back and tried again next time
		$this->assertSame(static::latest(), $migrations->version());
		$this->assertSame([99], array_keys($migrations->pending()));
		$this->assertArrayNotHasKey('idx_test', $this->schema($log)['indices']);
	}

	public function testVersions(): void
	{
		// versions must be consecutive, starting with 1
		$versions = array_keys(static::versions());
		$this->assertSame(range(1, count($versions)), $versions);
	}

	protected static function versions(): array
	{
		return (new ReflectionClassConstant(Migrations::class, 'VERSIONS'))->getValue();
	}
}
