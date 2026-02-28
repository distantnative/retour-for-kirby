<?php

namespace Kirby\Retour;

use Kirby\Toolkit\Date;
use PHPUnit\Framework\Attributes\CoversClass;
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

	public function testLabel(): void
	{
		$this->assertSame('1 January 2023', Timespan::label([
			'from' => '2023-01-01',
			'to'   => '2023-01-01',
			'unit' => 'day'
		]));

		$this->assertSame('January 2023', Timespan::label([
			'from' => '2023-01-01',
			'to'   => '2023-02-03',
			'unit' => 'month'
		]));

		$this->assertSame('2023', Timespan::label([
			'from' => '2023-01-01',
			'to'   => '2023-01-31',
			'unit' => 'year'
		]));

		$this->assertSame('1 - 15 January 2023', Timespan::label([
			'from' => '2023-01-01',
			'to'   => '2023-01-15',
			'unit' => 'days'
		]));

		$this->assertSame('1 January - 15 March 2023', Timespan::label([
			'from' => '2023-01-01',
			'to'   => '2023-03-15',
			'unit' => 'months'
		]));

		$this->assertSame('1 January 2022 - 15 March 2023', Timespan::label([
			'from' => '2022-01-01',
			'to'   => '2023-03-15',
			'unit' => 'months'
		]));
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
	/**
	 * Calls a protected static method on Timespan via reflection
	 */
	private function ts(string $method, mixed ...$args): mixed
	{
		$method = new ReflectionMethod(Timespan::class, $method);
		return $method->invoke(null, ...$args);
	}
}
