<?php

namespace Kirby\Retour\Panel;

use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Retour\Timespan;
use Kirby\Toolkit\Date;

class TimespanDialog extends DialogController
{
	protected function date(string|null $date): string|null
	{
		return Date::optional($date)?->format('Y-m-d');
	}

	public function load(): FormDialog
	{
		$selection = Timespan::selection();
		$min       = $this->date(Timespan::limits()[0]);
		$max       = $this->date('today');

		return new FormDialog(
			size: 'small',
			fields: [
				'from' => [
					'type'     => 'date',
					'label'    => $this->i18n('retour.timespan.from.label'),
					'required' => true,
					'time'     => false,
					'min'      => $min,
					'max'      => $max
				],
				'to' => [
					'type'     => 'date',
					'label'    => $this->i18n('retour.timespan.to.label'),
					'required' => true,
					'time'     => false,
					'min'      => $min,
					'max'      => $max
				]
			],
			value: [
				'from' => max($this->date($selection['from']), $min),
				'to'   => min($this->date($selection['to']), $max)
			],
			submitButton: [
				'text' => $this->i18n('change')
			]
		);
	}
}
