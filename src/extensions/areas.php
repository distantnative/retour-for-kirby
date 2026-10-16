<?php

use Kirby\Panel\Panel;
use Kirby\Retour\Panel\View;

return [
	'retour' => fn () => [
		'label' => t('view.retour'),
		'icon'  => 'redirect',
		'menu'  => true,
		'link'  => 'retour/redirects',
		'views' => [
			[
				'pattern' => 'retour',
				'action'  => fn () => Panel::go('retour/redirects')
			],
			[
				'pattern' => 'retour/(redirects|failures)',
				'action'  => View::class
			]
		],
		'buttons' => require __DIR__ . '/buttons.php',
		'dialogs' => require __DIR__ . '/dialogs.php',
		'drawers' => require __DIR__ . '/drawers.php'
	]
];
