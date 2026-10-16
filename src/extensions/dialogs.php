<?php

use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Panel\Ui\Dialog\RemoveDialog;
use Kirby\Retour\Panel\FailureIgnoreDialog;
use Kirby\Retour\Panel\Id;
use Kirby\Retour\Panel\IgnoresDialog;
use Kirby\Retour\Panel\TimespanDialog;
use Kirby\Retour\Retour;

return [
	'retour.timespan' => [
		'pattern' => 'retour/timespan',
		'action'  => TimespanDialog::class
	],

	'retour.redirect.delete' => [
		'pattern' => 'retour/redirects/(:any)/delete',
		'load'    => fn () => new RemoveDialog(
			text: t('field.structure.delete.confirm')
		),
		'submit' => function (string $id) {
			$redirects = Retour::instance()->redirects();
			$redirects->remove(urldecode($id));
			$redirects->save();
			return true;
		}
	],

	'retour.failure.delete' => [
		'pattern' => 'retour/failures/(:any)/delete',
		'load'    => fn () => new RemoveDialog(
			text: t('field.structure.delete.confirm')
		),
		'submit' => fn (string $path) => Retour::instance()->log()->remove(Id::toPath($path))
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
		'load'    => fn () => new FormDialog(
			fields: [
				'mode' => [
					'type'    => 'radio',
					'label'   => t('retour.failures.clear.confirm'),
					'help'    => t('retour.failures.clear.help'),
					'options' => [
						[
							'value' => 'all',
							'text'  => t('retour.failures.clear.mode.all')
						],
						[
							'value' => 'failures',
							'text'  => t('retour.failures.clear.mode.failures')
						]
					]
				]
			],
			value: [
				'mode' => 'all'
			],
			submitButton: [
				'icon'  => 'trash',
				'text'  => t('retour.failures.clear'),
				'theme' => 'negative'
			]
		),
		'submit' => function () {
			$mode = get('mode');
			return Retour::instance()->log()->flush($mode);
		}
	]
];
