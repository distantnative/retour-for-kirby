<?php

namespace Kirby\Retour\Panel;

use DateInterval;
use DateTimeInterface;
use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Retour\Timespan;
use Kirby\Toolkit\Date;

class TimespanDialog extends DialogController
{
	protected function date(DateTimeInterface|string|null $date): string|null
	{
		return Date::optional($date)?->format('Y-m-d H:i:s');
	}

	protected function limits(): array
	{
		[$first] = Timespan::limits();

		return [
			$this->date($first),
			$this->date(Date::now()->add(new DateInterval('P1D')))
		];
	}

	public function load(): FormDialog
	{
		$selection   = Timespan::selection();
		[$min, $max] = $this->limits();

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
				'from' => $this->date($selection['from']),
				'to'   => $this->date($selection['to'])
			],
			submitButton: [
				'text' => $this->i18n('change')
			]
		);
	}
}
