<?php

namespace Kirby\Retour\Panel;

use DateInterval;
use DateTime;
use Kirby\Cms\App;
use Kirby\Panel\Panel;
use Kirby\Retour\Retour;
use Kirby\Retour\Timespan;
use Kirby\Toolkit\Date;
use Kirby\Toolkit\I18n;

class TimespanDialog
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

	public function load(): array
	{
		$selection   = Timespan::selection();
		[$min, $max] = $this->limits();

		return [
			'component' => 'k-form-dialog',
			'props'     => [
				'size'   => 'small',
				'fields' => [
					'from' => [
						'type'     => 'date',
						'label'    => t('retour.timespan.from.label'),
						'required' => true,
						'time'     => false,
						'min'      => $min,
						'max'      => $max
					],
					'to' => [
						'type'     => 'date',
						'label'    => t('retour.timespan.to.label'),
						'required' => true,
						'time'     => false,
						'min'      => $min,
						'max'      => $max
					]
				],
				'value' => [
					'from' => $this->date($selection['from']),
					'to'   => $this->date($selection['to'])
				],
				'submitButton' => [
					'text' => I18n::translate('change')
				]
			]
		];
	}

	public function submit(): array
	{
		$kirby   = App::instance();
		$data    = Timespan::query();
		$from    = Date::optional($data['from']);
		$to      = Date::optional($data['to']);

		if ($to->isBefore($from) === true) {
			$to = $from;
		}

		$kirby->session()->set('retour', [
			'from' => $from->format('Y-m-d'),
			'to'   => $to->format('Y-m-d')
		]);

		return [
			'redirect' => Panel::referrer()
		];
	}
}
