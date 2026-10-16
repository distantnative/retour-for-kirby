<?php

$dir = __DIR__ . '/Retour';

load([
	'Kirby\\Retour\\Config'      => $dir . '/Config.php',
	'Kirby\\Retour\\Ignores'     => $dir . '/Ignores.php',
	'Kirby\\Retour\\LogDisabled' => $dir . '/LogDisabled.php',
	'Kirby\\Retour\\Log'         => $dir . '/Log.php',
	'Kirby\\Retour\\Pattern'     => $dir . '/Pattern.php',
	'Kirby\\Retour\\Redirect'    => $dir . '/Redirect.php',
	'Kirby\\Retour\\Redirects'   => $dir . '/Redirects.php',
	'Kirby\\Retour\\Timespan'    => $dir . '/Timespan.php',

	'Kirby\\Retour\\Commands\\Indices' => $dir . '/Commands/Indices.php',
	'Kirby\\Retour\\Commands\\Seed'    => $dir . '/Commands/Seed.php',

	'Kirby\\Retour\\Panel\\FailureDeleteDialog' => $dir . '/Panel/FailureDeleteDialog.php',
	'Kirby\\Retour\\Panel\\FailureIgnoreDialog' => $dir . '/Panel/FailureIgnoreDialog.php',
	'Kirby\\Retour\\Panel\\FailuresFlushDialog' => $dir . '/Panel/FailuresFlushDialog.php',
	'Kirby\\Retour\\Panel\\Id'             => $dir . '/Panel/Id.php',
	'Kirby\\Retour\\Panel\\IgnoresDialog'  => $dir . '/Panel/IgnoresDialog.php',
	'Kirby\\Retour\\Panel\\RedirectDeleteDialog' => $dir . '/Panel/RedirectDeleteDialog.php',
	'Kirby\\Retour\\Panel\\TimespanDialog' => $dir . '/Panel/TimespanDialog.php',
	'Kirby\\Retour\\Panel\\RedirectCreateDrawer' => $dir . '/Panel/RedirectCreateDrawer.php',
	'Kirby\\Retour\\Panel\\RedirectEditDrawer' => $dir . '/Panel/RedirectEditDrawer.php',
	'Kirby\\Retour\\Panel\\FailureResolveDrawer' => $dir . '/Panel/FailureResolveDrawer.php',
	'Kirby\\Retour\\Panel\\View'       => $dir . '/Panel/View.php',

	'Kirby\\Retour\\Retour'      => $dir . '/Retour.php'
]);
