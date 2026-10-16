<?php

namespace Kirby\Retour;

use DateTimeImmutable;
use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
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

		// weeks and custom ranges show their first and last day

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
		$log      = $retour->log();

		return [
			$log?->first()['date'] ?? null,
			$log?->last()['date'] ?? null
		];
	}

	protected static function month(Date $date): string|null
	{
		$month = $date->format('F');
		$month = lcfirst($month);
		return I18n::translate('months.' . $month);
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
}
