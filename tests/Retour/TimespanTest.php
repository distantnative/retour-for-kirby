<?php

namespace Kirby\Retour;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Date;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;

#[CoversClass(Timespan::class)]
class TimespanTest extends TestCase
{
	public function testChecks(): void
	{
		$data = [
			'from'  => '2023-01-01',
			'to'    => '2023-01-31',
			'first' => '2023-01-01',
			'last'  => '2023-12-31',
		];

		$checks = $this->ts('checks', $data);

		$this->assertArrayHasKey('hasAll', $checks);
		$this->assertArrayHasKey('hasNext', $checks);
		$this->assertArrayHasKey('hasPrev', $checks);
		$this->assertArrayHasKey('isAll', $checks);
		$this->assertArrayHasKey('isCurrent', $checks);

		// first and last both exist → hasAll is true
		$this->assertTrue($checks['hasAll']);

		// no first/last → hasAll is false
		$noChecks = $this->ts('checks', array_merge($data, ['first' => null, 'last' => null]));
		$this->assertFalse($noChecks['hasAll']);
	}

	public function testHasNext(): void
	{
		$to    = Date::optional('2023-01-15');
		$today = Date::optional('2023-06-01');

		// last exists and is after to → has next
		$this->assertTrue($this->ts('hasNext', $to, $today, Date::optional('2023-06-30')));

		// last and today are both before to → no next
		$this->assertFalse($this->ts(
			'hasNext',
			Date::optional('2024-12-31'), // to in the future
			$today,                       // today = 2023-06-01
			Date::optional('2022-12-01')  // last before both to and today
		));

		// no last recorded → no next
		$this->assertFalse($this->ts('hasNext', $to, $today, null));

		// last equals to but today is after to → still has next
		$this->assertTrue($this->ts('hasNext', $to, $today, Date::optional('2023-01-15')));
	}

	public function testHasPrev(): void
	{
		$from  = Date::optional('2023-06-01');
		$first = Date::optional('2023-01-01');

		// first exists and is before from → has prev
		$this->assertTrue($this->ts('hasPrev', $from, $first));

		// first exists but equals from → no prev
		$this->assertFalse($this->ts('hasPrev', $from, Date::optional('2023-06-01')));

		// first is after from → no prev
		$this->assertFalse($this->ts('hasPrev', $from, Date::optional('2023-07-01')));

		// no first recorded → no prev
		$this->assertFalse($this->ts('hasPrev', $from, null));
	}

	public function testIsAll(): void
	{
		$from  = Date::optional('2023-01-01');
		$to    = Date::optional('2023-12-31');
		$first = Date::optional('2023-01-01');
		$last  = Date::optional('2023-12-31');

		// from==first and to==last → is all
		$this->assertTrue($this->ts('isAll', $from, $to, $first, $last));

		// from doesn't match first → not all
		$this->assertFalse($this->ts('isAll', Date::optional('2023-02-01'), $to, $first, $last));

		// no first → not all
		$this->assertFalse($this->ts('isAll', $from, $to, null, $last));

		// no last → not all
		$this->assertFalse($this->ts('isAll', $from, $to, $first, null));
	}

	public function testIsCurrent(): void
	{
		$today = Date::optional('2023-06-15');

		// range contains today → is current
		$this->assertTrue($this->ts(
			'isCurrent',
			Date::optional('2023-06-01'),
			Date::optional('2023-06-30'),
			$today
		));

		// range is entirely in the past → not current
		$this->assertFalse($this->ts(
			'isCurrent',
			Date::optional('2023-01-01'),
			Date::optional('2023-03-31'),
			$today
		));

		// range is entirely in the future → not current
		$this->assertFalse($this->ts(
			'isCurrent',
			Date::optional('2023-07-01'),
			Date::optional('2023-12-31'),
			$today
		));

		// today is the only day in the range → is current
		$this->assertTrue($this->ts('isCurrent', $today, $today, $today));
	}

	public function testSelection(): void
	{
		$this->kirby->clone([
			'request' => [
				'query' => [
					'from' => '2024-01-01',
					'to'   => '2024-01-31'
				]
			]
		]);

		$expected = ['from' => '2024-01-01', 'to' => '2024-01-31'];
		$this->assertSame($expected, Timespan::selection());
		$this->assertSame($expected, App::instance()->session()->get('retour'));
	}

	public function testSelectionDefault(): void
	{
		$this->assertSame([
			'from' => date('Y-m-01'),
			'to'   => date('Y-m-t')
		], Timespan::selection());
	}

	public function testSelectionIgnoresInvalidQuery(): void
	{
		$app = $this->kirby->clone([
			'request' => [
				'query' => [
					'from' => 'x',
					'to'   => '2024-01-31'
				]
			]
		]);

		$stored = ['from' => '2023-05-01', 'to' => '2023-05-31'];
		$app->session()->set('retour', $stored);

		$this->assertSame($stored, Timespan::selection());
		$this->assertSame($stored, $app->session()->get('retour'));
	}

	public function testSelectionIgnoresInvalidSession(): void
	{
		$this->kirby->session()->set('retour', [
			'from' => '-200000-01-01',
			'to'   => '2024-01-31'
		]);

		$this->assertSame([
			'from' => date('Y-m-01'),
			'to'   => date('Y-m-t')
		], Timespan::selection());
	}

	public function testSet(): void
	{
		$data = Timespan::set(['from' => '2024-02-01', 'to' => '2024-02-29']);
		$this->assertSame(['from' => '2024-02-01', 'to' => '2024-02-29'], $data);
		$this->assertSame($data, $this->kirby->session()->get('retour'));

		// `to` before `from`
		$data = Timespan::set(['from' => '2024-02-10', 'to' => '2024-02-01']);
		$this->assertSame(['from' => '2024-02-10', 'to' => '2024-02-10'], $data);
		$this->assertSame($data, $this->kirby->session()->get('retour'));
	}

	public static function invalidProvider(): array
	{
		return [
			'strings'        => [['from' => 'x', 'to' => 'y']],
			'arrays'         => [['from' => ['2024-01-01'], 'to' => '2024-01-31']],
			'missing'        => [['from' => '2024-01-01']],
			'null'           => [['from' => null, 'to' => '2024-01-31']],
			'negative year'  => [['from' => '-200000-01-01', 'to' => '2024-01-31']],
			'five-digit year' => [['from' => '2024-01-01', 'to' => '20000-01-01']],
			'two-digit year' => [['from' => '24-01-01', 'to' => '2024-01-31']],
			'too early'      => [['from' => '2018-12-31', 'to' => '2024-01-31']],
			'too late'       => [['from' => '2024-01-01', 'to' => '2100-01-01']],
			'overflow'       => [['from' => '2024-02-30', 'to' => '2024-03-31']],
			'no padding'     => [['from' => '2024-1-1', 'to' => '2024-01-31']],
			'with time'      => [['from' => '2024-01-01 00:00:00', 'to' => '2024-01-31']],
		];
	}

	#[DataProvider('invalidProvider')]
	public function testSetInvalid(array $data): void
	{
		$stored = ['from' => '2023-05-01', 'to' => '2023-05-31'];
		$this->kirby->session()->set('retour', $stored);

		try {
			Timespan::set($data);
			$this->fail('Invalid dates must be rejected');
		} catch (InvalidArgumentException) {
			$this->assertSame($stored, $this->kirby->session()->get('retour'));
		}
	}

	public function testSetLimits(): void
	{
		$data = ['from' => '2019-01-01', 'to' => '2099-12-31'];
		$this->assertSame($data, Timespan::set($data));
	}

	public function testUnit(): void
	{
		$this->assertSame('day', Timespan::unit([
			'from' => '2023-01-01',
			'to'   => '2023-01-01',
		]));

		$this->assertSame('days', Timespan::unit([
			'from' => '2023-01-01',
			'to'   => '2023-02-03',
		]));

		$this->assertSame('month', Timespan::unit([
			'from' => '2023-01-01',
			'to'   => '2023-01-31',
		]));

		$this->assertSame('months', Timespan::unit([
			'from' => '2023-01-01',
			'to'   => '2023-03-31',
		]));

		$this->assertSame('year', Timespan::unit([
			'from' => '2023-01-01',
			'to'   => '2023-12-31',
		]));
	}

	public static function unitWeekProvider(): array
	{
		return [
			// English (US) weeks start on Sunday
			[null, '2023-01-01', '2023-01-07', 'week'],
			[null, '2023-01-02', '2023-01-08', 'days'],

			// across months and years
			[1, '2023-01-30', '2023-02-05', 'week'],
			[1, '2024-12-30', '2025-01-05', 'week'],

			// seven days that don't start on the first weekday
			[1, '2023-01-01', '2023-01-07', 'days'],
			[6, '2023-01-02', '2023-01-08', 'days'],

			// more or less than seven days
			[1, '2023-01-02', '2023-01-07', 'days'],
			[1, '2023-01-02', '2023-01-09', 'days'],
		];
	}

	#[DataProvider('unitWeekProvider')]
	public function testUnitWeek(
		int|null $weekday,
		string $from,
		string $to,
		string $expected
	): void {
		// weeks start on the same day as in the Panel's calendar
		$this->app(['date.weekday' => $weekday]);

		$this->assertSame($expected, Timespan::unit([
			'from' => $from,
			'to'   => $to,
		]));
	}

	public function testUnitWeekDaylightSavingTime(): void
	{
		$timezone = date_default_timezone_get();
		date_default_timezone_set('Europe/Berlin');

		try {
			$this->app(['date.weekday' => 0]);

			// clocks go forward
			$this->assertSame('week', Timespan::unit([
				'from' => '2023-03-26',
				'to'   => '2023-04-01',
			]));

			// clocks go back
			$this->assertSame('week', Timespan::unit([
				'from' => '2023-10-29',
				'to'   => '2023-11-04',
			]));
		} finally {
			date_default_timezone_set($timezone);
		}
	}

	/**
	 * Calls a protected static method on Timespan via reflection
	 */
	private function ts(string $method, mixed ...$args): mixed
	{
		$method = new ReflectionMethod(Timespan::class, $method);
		return $method->invoke(null, ...$args);
	}
}
