<?php

use Kirby\Retour\Panel\FailureResolveDrawer;
use Kirby\Retour\Panel\RedirectCreateDrawer;
use Kirby\Retour\Panel\RedirectEditDrawer;

return [
	'retour.redirect.create' => [
		'pattern' => 'retour/redirects/create',
		'action'  => RedirectCreateDrawer::class
	],

	'retour.redirect.edit' => [
		'pattern' => 'retour/redirects/(:any)/edit',
		'action'  => RedirectEditDrawer::class
	],

	'retour.failure.resolve' => [
		'pattern' => 'retour/failures/(:any)/resolve',
		'action'  => FailureResolveDrawer::class
	]
];
