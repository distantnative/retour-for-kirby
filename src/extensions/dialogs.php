<?php

use Kirby\Retour\Panel\FailureDeleteDialog;
use Kirby\Retour\Panel\FailureIgnoreDialog;
use Kirby\Retour\Panel\FailuresFlushDialog;
use Kirby\Retour\Panel\IgnoresDialog;
use Kirby\Retour\Panel\RedirectDeleteDialog;
use Kirby\Retour\Panel\TimespanDialog;

return [
	'retour.timespan' => [
		'pattern' => 'retour/timespan',
		'action'  => TimespanDialog::class
	],

	'retour.redirect.delete' => [
		'pattern' => 'retour/redirects/(:any)/delete',
		'action'  => RedirectDeleteDialog::class
	],

	'retour.failure.delete' => [
		'pattern' => 'retour/failures/(:any)/delete',
		'action'  => FailureDeleteDialog::class
	],

	'retour.failure.ignore' => [
		'pattern' => 'retour/failures/(:any)/ignore',
		'action'  => FailureIgnoreDialog::class
	],

	'retour.failures.ignores' => [
		'pattern' => 'retour/failures/ignores',
		'action'  => IgnoresDialog::class
	],

	'retour.failures.flush' => [
		'pattern' => 'retour/failures/flush',
		'action'  => FailuresFlushDialog::class
	]
];
