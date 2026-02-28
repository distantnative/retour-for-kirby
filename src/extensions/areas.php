<?php

use Kirby\Panel\Panel;
use Kirby\Retour\Panel\View;

return [
	'retour' => fn () => [
		'label' => t('view.retour'),
		'icon'  => 'shuffle',
		'menu'  => true,
		'link'  => 'retour/redirects',
		'views' => [
			[
				'pattern' => 'retour',
				'action'  => fn () => Panel::go('retour/redirects')
			],
			[
				'pattern' => 'retour/(:any)',
				'action'  => fn (string $tab) => View::tab($tab)
			]
		],
		'dialogs' => require __DIR__ . '/dialogs.php',
		'drawers' => require __DIR__ . '/drawers.php'
	]
];
