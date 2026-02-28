<?php

namespace Kirby\Retour;

use Kirby\Cms\App;
use Kirby\Toolkit\Date;
use Kirby\Toolkit\I18n;

/**
 * Manages the selected date range for log views,
 * including session persistence and label/unit resolution
 */
class Timespan
{
	protected static function checks(array $data): array
	{
		$today = Date::now();
		$from  = Date::optional($data['from']);
		$to    = Date::optional($data['to']);
		$first = Date::optional($data['first']);
		$last  = Date::optional($data['last']);

		return [
			// either has more data in the future
			// or today is in the future
			'hasAll'    => $first && $last,
			'hasNext'   => static::hasNext($to, $today, $last),
			'hasPrev'   => static::hasPrev($from, $first),
			'isAll'     => static::isAll($from, $to, $first, $last),
			'isCurrent' => static::isCurrent($from, $to, $today),
		];
	}

	protected static function hasNext(
		Date $to,
		Date $today,
		Date|null $last,
	): bool {
		if ($last === null) {
			return false;
		}

		return $to < $last || $to < $today;
	}

	protected static function hasPrev(
		Date $from,
		Date|null $first,
	): bool {
		if ($first === null) {
			return false;
		}

		return $from > $first;
	}

	protected static function isAll(
		Date $from,
		Date $to,
		Date|null $first,
		Date|null $last,
	): bool {
		if ($first === null || $last === null) {
			return false;
		}

		return $from->format('Y-m-d') === $first->format('Y-m-d') &&
			   $to->format('Y-m-d') === $last->format('Y-m-d');
	}

	protected static function isCurrent(
		Date $from,
		Date $to,
		Date $today
	): bool {
		return $from <= $today && $to >= $today;
	}

	public static function label(array $data): string
	{
		$unit = $data['unit'];
		$from = Date::optional($data['from']);
		$to   = Date::optional($data['to']);

		if ($unit === 'day') {
			return sprintf(
				'%s %s %s',
				$from->format('j'),
				static::month($from),
				$from->format('Y')
			);
		}

		if ($unit === 'month') {
			return sprintf(
				'%s %s',
				static::month($from),
				$from->format('Y')
			);
		}

		if ($unit === 'year') {
			return $from->format('Y');
		}

		// within same month
		if ($from->format('Y-m') === $to->format('Y-m')) {
			return sprintf(
				'%s - %s %s %s',
				$from->format('j'),
				$to->format('j'),
				static::month($from),
				$from->format('Y')
			);
		}

		// within same year
		if ($from->format('Y') === $to->format('Y')) {
			return sprintf(
				'%s %s - %s %s %s',
				$from->format('j'),
				static::month($from),
				$to->format('j'),
				static::month($to),
				$from->format('Y')
			);
		}

		return sprintf(
			'%s %s %s - %s %s %s',
			$from->format('j'),
			static::month($from),
			$from->format('Y'),
			$to->format('j'),
			static::month($to),
			$to->format('Y')
		);
	}

	public static function limits(Retour|null $retour = null): array
	{
		$retour ??= Retour::instance();

		return [
			$retour->log()->first()['date'] ?? null,
			$retour->log()->last()['date'] ?? null
		];
	}

	protected static function month(Date $date): string|null
	{
		$month = $date->format('F');
		$month = lcfirst($month);
		return I18n::translate('months.' . $month);
	}

	public static function props(): array
	{
		$data = static::selection();
		[$data['first'], $data['last']] = static::limits();

		$data['unit']  = static::unit($data);
		$data['label'] = static::label($data);

		return $data + static::checks($data);
	}

	public static function query(): array
	{
		return App::instance()->request()->get(['from', 'to']);
	}

	public static function selection(): array
	{
		$data = static::query();

		if ($data['from'] !== null && $data['to']  !== null) {
			// get timespan from query parameters
			static::set($data);

		} else {
			// otherwise try to get from session
			$data = App::instance()->session()->get('retour');
		}

		// if no data exists, use current month
		if ($data === null) {
			$data = [
				'from' => date('Y-m-01'),
				'to'   => date('Y-m-t')
			];
		}

		return $data;
	}

	public static function set(array $data): void
	{
		App::instance()->session()->set('retour', $data);
	}

	/**
	 * Returns the appropriate date unit for a given timespan
	 */
	public static function unit(array $data): string
	{
		$from = Date::optional($data['from']);
		$to   = Date::optional($data['to']);

		// full units
		if ($from->format('Y-m-d') === $to->format('Y-m-d')) {
			return 'day';
		}

		if (
			$from->format('Y-m') === $to->format('Y-m') &&
			$from->format('j') === '1' &&
			$to->format('j') === $to->format('t')
		) {
			return 'month';
		}

		if (
			$from->format('Y') === $to->format('Y') &&
			$from->format('n-j') === '1-1' &&
			$to->format('n-j') === '12-31'
		) {
			return 'year';
		}

		// custom ranges
		if ($from->diff($to)->days > 50) {
			return 'months';
		}

		return 'days';
	}
}
