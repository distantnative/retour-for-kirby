<?php

load([
	'Kirby\\Retour\\Config'    => 'Config.php',
	'Kirby\\Retour\\Ignores'   => 'Ignores.php',
	'Kirby\\Retour\\Log'       => 'Log.php',
	'Kirby\\Retour\\Pattern'   => 'Pattern.php',
	'Kirby\\Retour\\Redirect'  => 'Redirect.php',
	'Kirby\\Retour\\Redirects' => 'Redirects.php',
	'Kirby\\Retour\\Retour'    => 'Retour.php',
	'Kirby\\Retour\\Timespan'  => 'Timespan.php',

	'Kirby\\Retour\\Commands\\Indices' => 'Commands/Indices.php',
	'Kirby\\Retour\\Commands\\Seed'    => 'Commands/Seed.php',

	'Kirby\\Retour\\Panel\\FailureDeleteDialog'  => 'Panel/FailureDeleteDialog.php',
	'Kirby\\Retour\\Panel\\FailureIgnoreDialog'  => 'Panel/FailureIgnoreDialog.php',
	'Kirby\\Retour\\Panel\\FailureResolveDrawer' => 'Panel/FailureResolveDrawer.php',
	'Kirby\\Retour\\Panel\\FailuresFlushDialog'  => 'Panel/FailuresFlushDialog.php',
	'Kirby\\Retour\\Panel\\Id'                   => 'Panel/Id.php',
	'Kirby\\Retour\\Panel\\IgnoresDialog'        => 'Panel/IgnoresDialog.php',
	'Kirby\\Retour\\Panel\\RedirectCreateDrawer' => 'Panel/RedirectCreateDrawer.php',
	'Kirby\\Retour\\Panel\\RedirectDeleteDialog' => 'Panel/RedirectDeleteDialog.php',
	'Kirby\\Retour\\Panel\\RedirectEditDrawer'   => 'Panel/RedirectEditDrawer.php',
	'Kirby\\Retour\\Panel\\TimespanDialog'       => 'Panel/TimespanDialog.php',
	'Kirby\\Retour\\Panel\\View'                 => 'Panel/View.php',
], __DIR__ . '/Retour');
