<?php

use Kirby\CLI\CLI;
use Kirby\Retour\Retour;

return [
	'retour:indices' => [
		'description' => 'Add indices to the Retour log SQLite database and remove unused ones',
		'command'     => function (CLI $cli): void {
			$kirby  = $cli->kirby();
			$retour = Retour::instance($kirby);

			if ($retour->hasLog() === false) {
				$cli->out('Logging is disabled, no database to update.');
				return;
			}

			$db = $retour->log()->database();
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
	]
];
