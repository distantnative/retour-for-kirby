<?php

namespace Kirby\Retour;


/**
 * @coversDefaultClass \Kirby\Retour\Timespan
 */
class TimespanTest extends TestCase
{
    /**
     * @covers ::label
     */
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

    /**
     * @covers ::unit
     */
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
}
