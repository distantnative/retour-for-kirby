<?php

use Kirby\Panel\Ui\Button\ViewButton;

return [
	'retour.redirects.create' => fn () => new ViewButton(
		drawer: 'retour/redirects/create',
		icon: 'add',
		text: t('add')
	),
	'retour.failures.ignores' => fn () => new ViewButton(
		dialog: 'retour/failures/ignores',
		icon: 'muted',
		text: t('retour.failures.ignored')
	),
	'retour.failures.flush' => fn () => new ViewButton(
		dialog: 'retour/failures/flush',
		icon: 'clear',
		text: t('retour.failures.clear')
	)
];
