<?php

namespace Kirby\Retour;

use DateTimeImmutable;
use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Date;

/**
 * Date range of the log views, kept in the session
 */
class Timespan
{
	/**
	 * First and last day of the `day`, `week`, `month` or `year` around the date
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
		$today = Date::today();
		$from  = Date::optional($data['from']);
		$to    = Date::optional($data['to']);
		$first = Date::optional($data['first']);
		$last  = Date::optional($data['last'])?->floor('day');

		return [
			'hasAll'    => $first && $last,
			'hasNext'   => static::hasNext($to, $today, $last),
			'hasPrev'   => static::hasPrev($from, $first),
			'isAll'     => static::isAll($from, $to, $first, $last),
			'isCurrent' => static::isCurrent($from, $to, $today),
		];
	}

	protected static function date(mixed $date): string|null
	{
		if (is_string($date) === false) {
			return null;
		}

		$parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

		// the round trip rejects overflows (e.g. Feb 30) and non-four-digit years
		if ($parsed === false || $parsed->format('Y-m-d') !== $date) {
			return null;
		}

		// no logs predate Retour
		if ($date < '2019-01-01') {
			return null;
		}

		return $date;
	}

	/**
	 * 0 = Sunday … 6 = Saturday, as in the Panel's calendar
	 */
	protected static function firstWeekday(): int
	{
		return Date::firstWeekday(App::instance()->translation()->locale());
	}

	protected static function hasNext(
		Date $to,
		Date $today,
		Date|null $last,
	): bool {
		return $last !== null && ($to < $last || $to < $today);
	}

	protected static function hasPrev(
		Date $from,
		Date|null $first,
	): bool {
		return $first !== null && $from > $first;
	}

	protected static function isAll(
		Date $from,
		Date $to,
		Date|null $first,
		Date|null $last,
	): bool {
		return
			$first !== null &&
			$last !== null &&
			$from->format('Y-m-d') === $first->format('Y-m-d') &&
			$to->format('Y-m-d') === $last->format('Y-m-d');
	}

	protected static function isCurrent(
		Date $from,
		Date $to,
		Date $today
	): bool {
		return $from <= $today && $to >= $today;
	}

	public static function limits(): array
	{
		$log = Retour::instance()->log();

		return [
			$log?->first()['date'] ?? null,
			$log?->last()['date'] ?? null
		];
	}

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

		// with a stats entry per month, timespans only reach
		// as far into the future as the current year needs
		$max  = date('Y-m-d', strtotime('+1 year'));
		$from = min($from, $max);

		return [
			'from' => $from,
			'to'   => max($from, min($to, $max))
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
			'now'   => date('Y-m-d H:i:s'),
			'prev'  => $data['hasPrev'] && $data['isAll'] === false ? static::step($data, -1) : null,
			'next'  => $data['hasNext'] && $data['isAll'] === false ? static::step($data, 1) : null,
			'units' => static::units($data)
		];
	}

	public static function selection(): array
	{
		$kirby = App::instance();
		$query = static::normalize($kirby->request()->get(['from', 'to']));

		if ($query !== null) {
			return static::set($query);
		}

		return static::normalize($kirby->session()->get('retour')) ?? [
			'from' => date('Y-m-01'),
			'to'   => date('Y-m-t')
		];
	}

	/**
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
	 * Previous (`-1`) or next (`1`) timespan; custom ranges move by their own length
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

	public static function unit(array $data): string
	{
		$from = Date::optional($data['from']);
		$to   = Date::optional($data['to']);

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

		if ($from->diff($to)->days > 50) {
			return 'months';
		}

		return 'days';
	}

	/**
	 * Dropdown targets: units around the start, the current unit
	 * around today; `all` is `null` without logs
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

		$units['today'] = static::around($today, match ($data['unit']) {
			'days'   => 'day',
			'months' => 'month',
			default  => $data['unit']
		});

		return $units;
	}
}
