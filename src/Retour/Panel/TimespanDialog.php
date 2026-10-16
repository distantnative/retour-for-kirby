<?php

namespace Kirby\Retour\Panel;

use DateInterval;
use DateTimeInterface;
use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Retour\Timespan;
use Kirby\Toolkit\Date;

/**
 * Dialog to pick the date range of the log views
 */
class TimespanDialog extends DialogController
{
	protected function date(
		DateTimeInterface|string|null $date,
		string $format = 'Y-m-d H:i:s'
	): string|null {
		return Date::optional($date)?->format($format);
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
