<?php

namespace Kirby\Retour;

use DateTimeImmutable;
use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Date;

/**
 * Manages the selected date range for log views,
 * including session persistence and unit resolution
 */
class Timespan
{
	/**
	 * Returns the first and last day of the unit
	 * (`day`, `week`, `month` or `year`) around the date
	 */
	public static function around(string $date, string $unit): array
	{
		$date = new DateTimeImmutable($date);

		$from = match ($unit) {
			'day'   => $date,
			'week'  => $date->modify('-' . ((int)$date->format('w') - static::firstWeekday() + 7) % 7 . ' days'),
			'month' => $date->modify('first day of this month'),
			'year'  => $date->setDate((int)$date->format('Y'), 1, 1)
		};

		$to = match ($unit) {
			'day'   => $from,
			'week'  => $from->modify('+6 days'),
			'month' => $from->modify('last day of this month'),
			'year'  => $from->setDate((int)$from->format('Y'), 12, 31)
		};

		return [
			'from' => $from->format('Y-m-d'),
			'to'   => $to->format('Y-m-d')
		];
	}

	protected static function checks(array $data): array
	{
		$today = Date::now();
		$from  = Date::optional($data['from']);
		$to    = Date::optional($data['to']);
		$first = Date::optional($data['first']);
		$last  = Date::optional($data['last']);

		return [
			'hasAll'    => $first && $last,
			'hasNext'   => static::hasNext($to, $today, $last),
			'hasPrev'   => static::hasPrev($from, $first),
			'isAll'     => static::isAll($from, $to, $first, $last),
			'isCurrent' => static::isCurrent($from, $to, $today),
		];
	}

	/**
	 * Returns the date if it is a valid `YYYY-MM-DD` string
	 */
	protected static function date(mixed $date): string|null
	{
		if (is_string($date) === false) {
			return null;
		}

		$parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

		// the round trip rejects overflowing dates (e.g. Feb 30)
		// as well as years with more or less than four digits
		if ($parsed === false || $parsed->format('Y-m-d') !== $date) {
			return null;
		}

		// there are no logs from before Retour existed, and as the
		// stats have an entry per month, timespans like 0000–9999
		// would create ~120k of them
		if ($date < '2019-01-01' || $date > '2099-12-31') {
			return null;
		}

		return $date;
	}

	/**
	 * Returns the first day of the week (0 = Sunday … 6 = Saturday)
	 * for the Panel language, as used by the Panel's calendar
	 */
	protected static function firstWeekday(): int
	{
		$locale = App::instance()->translation()->locale();
		return Date::firstWeekday($locale);
	}

	/**
	 * Whether the log has newer entries or today is after the timespan
	 */
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

	public static function limits(Retour|null $retour = null): array
	{
		$retour ??= Retour::instance();
		$log      = $retour->log();

		return [
			$log?->first()['date'] ?? null,
			$log?->last()['date'] ?? null
		];
	}

	/**
	 * Returns the timespan with valid dates (and `to` not
	 * before `from`) or `null` if any of the dates is invalid
	 */
	protected static function normalize(mixed $data): array|null
	{
		if (is_array($data) === false) {
			return null;
		}

		$from = static::date($data['from'] ?? null);
		$to   = static::date($data['to'] ?? null);

		if ($from === null || $to === null) {
			return null;
		}

		if ($to < $from) {
			$to = $from;
		}

		return [
			'from' => $from,
			'to'   => $to
		];
	}

	public static function props(): array
	{
		$data = static::selection();
		[$data['first'], $data['last']] = static::limits();

		$data['unit'] = static::unit($data);
		$data        += static::checks($data);

		return [
			...$data,
			// where the previous and next buttons
			// and the units of the dropdown lead to
			'prev'  => $data['hasPrev'] && $data['isAll'] === false ? static::step($data, -1) : null,
			'next'  => $data['hasNext'] && $data['isAll'] === false ? static::step($data, 1) : null,
			'units' => static::units($data)
		];
	}

	public static function query(): array
	{
		return App::instance()->request()->get(['from', 'to']);
	}

	public static function selection(): array
	{
		$query = static::normalize(static::query());

		// get timespan from valid query parameters
		if ($query !== null) {
			return static::set($query);
		}

		// otherwise try to get from session
		$session = App::instance()->session()->get('retour');

		// if no valid data exists, use current month
		return static::normalize($session) ?? [
			'from' => date('Y-m-01'),
			'to'   => date('Y-m-t')
		];
	}

	/**
	 * Stores the timespan in the session
	 *
	 * @throws InvalidArgumentException If a date is not a valid `YYYY-MM-DD` string
	 */
	public static function set(array $data): array
	{
		$data = static::normalize($data) ?? throw new InvalidArgumentException(
			message: 'The timespan requires valid dates in the format YYYY-MM-DD'
		);

		App::instance()->session()->set('retour', $data);
		return $data;
	}

	/**
	 * Returns the timespan before (`-1`) or after (`1`) this one:
	 * the previous or next unit, custom ranges move by their own length
	 */
	public static function step(array $data, int $direction): array
	{
		$from = new DateTimeImmutable($data['from']);
		$to   = new DateTimeImmutable($data['to']);

		if ($data['unit'] === 'days') {
			$days = $direction * ($from->diff($to)->days + 1);

			return [
				'from' => $from->modify($days . ' days')->format('Y-m-d'),
				'to'   => $to->modify($days . ' days')->format('Y-m-d')
			];
		}

		// whole months, also when the range starts or ends within one
		if ($data['unit'] === 'months') {
			$from   = $from->modify('first day of this month');
			$to     = $to->modify('first day of this month');
			$months = $direction * ($from->diff($to)->y * 12 + $from->diff($to)->m + 1);

			return [
				'from' => $from->modify($months . ' months')->format('Y-m-d'),
				'to'   => $to->modify($months . ' months')->modify('last day of this month')->format('Y-m-d')
			];
		}

		return static::around($from->modify($direction . ' ' . $data['unit'])->format('Y-m-d'), $data['unit']);
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
			(int)$from->format('w') === static::firstWeekday() &&
			$from->diff($to)->days === 6
		) {
			return 'week';
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

	/**
	 * Returns the timespans that the units of the dropdown lead to:
	 * a unit around the start of the timespan, the current unit
	 * again around today; `null` for all without logs
	 */
	protected static function units(array $data): array
	{
		$today = date('Y-m-d');
		$units = [
			'all' => $data['hasAll'] ? [
				'from' => Date::optional($data['first'])->format('Y-m-d'),
				'to'   => Date::optional($data['last'])->format('Y-m-d')
			] : null
		];

		foreach (['year', 'month', 'week', 'day'] as $unit) {
			$units[$unit] = static::around($unit === $data['unit'] ? $today : $data['from'], $unit);
		}

		// custom ranges go to today or its month
		$units['today'] = static::around($today, match ($data['unit']) {
			'days'   => 'day',
			'months' => 'month',
			default  => $data['unit']
		});

		return $units;
	}
}
