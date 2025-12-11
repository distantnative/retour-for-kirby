<?php

use Kirby\Retour\Retour;

return [
	'retour:indices' => [
		'description' => 'Add indices to SQLite database',
		'command'     => function ($cli) {
			// $retour = new Retour();
			// $db     = $retour->log()->database();
			// $cli->out('Adding indices to ' . $db->name());
		}
	]
];
