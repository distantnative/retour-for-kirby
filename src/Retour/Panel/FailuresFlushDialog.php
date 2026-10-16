<?php

namespace Kirby\Retour\Panel;

use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Retour\Retour;

/**
 * Clears the complete log or only its failures
 */
class FailuresFlushDialog extends DialogController
{
	public function load(): FormDialog
	{
		return new FormDialog(
			fields: [
				'mode' => [
					'type'    => 'radio',
					'label'   => $this->i18n('retour.failures.clear.confirm'),
					'help'    => $this->i18n('retour.failures.clear.help'),
					'options' => [
						[
							'value' => 'all',
							'text'  => $this->i18n('retour.failures.clear.mode.all')
						],
						[
							'value' => 'failures',
							'text'  => $this->i18n('retour.failures.clear.mode.failures')
						]
					]
				]
			],
			value: [
				'mode' => 'all'
			],
			submitButton: [
				'icon'  => 'clear',
				'text'  => $this->i18n('retour.failures.clear'),
				'theme' => 'negative'
			]
		);
	}

	public function submit(): bool
	{
		$mode = $this->request->get('mode') ?? '';
		return Retour::instance()->log()?->flush($mode) === true;
	}
}
