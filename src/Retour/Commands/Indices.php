<?php

namespace Kirby\Retour\Commands;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;
use Kirby\Retour\Retour;

/**
 * Adds indices to the log database
 * to speed up queries on large logs
 */
class Indices extends Command
{
	public static function command(CLI $cli): void
	{
		$log = Retour::instance($cli->kirby())->log();

		if ($log === null) {
			$cli->out('Logging is disabled, no database to update.');
			return;
		}

		$db = $log->database();
		$cli->out('Adding indices to ' . $db->name() . ' ...');

		$indices = [
			'idx_date'          => 'CREATE INDEX IF NOT EXISTS idx_date          ON records (date)',
			'idx_redirect_date' => 'CREATE INDEX IF NOT EXISTS idx_redirect_date ON records (redirect, date)',
		];

		foreach ($indices as $name => $sql) {
			$db->execute($sql);
			$cli->out('  ✓ ' . $name);
		}

		// earlier versions added indices that the
		// queries no longer need but enlarge the database
		$unused  = ['idx_fails', 'idx_path'];
		$removed = false;

		foreach ($unused as $name) {
			$exists = $db->query(
				'SELECT name FROM sqlite_master WHERE type = :type AND name = :name',
				['type' => 'index', 'name' => $name]
			)->count() > 0;

			if ($exists === true) {
				$db->execute('DROP INDEX ' . $name);
				$cli->out('  ✗ ' . $name . ' (unused)');
				$removed = true;
			}
		}

		// free the space of the removed indices
		if ($removed === true) {
			$cli->out('Shrinking the database ...');
			$db->execute('VACUUM');
		}

		$cli->success('Done.');
	}

	public static function description(): string|null
	{
		return 'Add indices to the Retour log SQLite database and remove unused ones';
	}
}
