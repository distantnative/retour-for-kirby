<?php

use Kirby\CLI\CLI;
use Kirby\Retour\Retour;

return [
	'retour:indices' => [
		'description' => 'Add indices to the Retour log SQLite database',
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
				'idx_path'          => 'CREATE INDEX IF NOT EXISTS idx_path          ON records (path)',
				'idx_redirect_date' => 'CREATE INDEX IF NOT EXISTS idx_redirect_date ON records (redirect, date)',
				'idx_fails'         => 'CREATE INDEX IF NOT EXISTS idx_fails         ON records (redirect, wasResolved, date)',
			];

			foreach ($indices as $name => $sql) {
				$db->execute($sql);
				$cli->out('  ✓ ' . $name);
			}

			$cli->success('Done.');
		}
	]
];
