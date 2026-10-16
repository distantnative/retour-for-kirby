<?php

namespace Kirby\Retour\Panel;

use DateInterval;
use DateTime;
use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Retour\Timespan;
use Kirby\Toolkit\Date;

class TimespanDialog extends DialogController
{
	public function date(
		DateTime|string|null $date,
		string $format = 'Y-m-d H:i:s'
	): string|null {
		if (is_string($date) === true) {
			$date = Date::optional($date);
		}

		return $date?->format($format);
	}

	public function limits(): array
	{
		[$limit] = Timespan::limits();
		$min     = $this->date($limit);
		$day     = DateInterval::createFromDateString('1 day');
		$max     = $this->date(Date::now()->add($day));
		return [$min, $max];
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

	public function submit(): array
	{
		$data = Timespan::query();

		// the date fields submit their values with a time
		foreach ($data as $key => $value) {
			if (is_string($value) === true) {
				$data[$key] = $this->date($value, 'Y-m-d');
			}
		}

		Timespan::set($data);

		return [
			'redirect' => $this->kirby->panel()->referrer()
		];
	}
}
