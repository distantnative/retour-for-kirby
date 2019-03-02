<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

class StatsTest extends TestCase
{
    protected function tearDown(): void
    {
        F::remove(str_replace('{x}', date('Y-m'), Stats::$file));
    }

    protected function _data(): array
    {
        return [
            [
                'status'   => 'redirected',
                'date'     => date('Y-m-d H:i')
            ],
            [
                'status'   => 'failed',
                'date'     => date('Y-m-d H:i')
            ],
            [
                'status'   => 'redirected',
                'date'     => date('Y-m-d H:i')
            ]
        ];
    }

    public function testDefaults(): void
    {
        $this->assertEquals([
            'day'   => [],
            'week'  => [],
            'month' => [],
        ], Stats::read());
    }

    public function testCount(): void
    {
        $count = Stats::count($this->_data());
        $this->assertTrue($count);

        $data = Stats::read(date('Y-m'));
        $this->assertEquals([
            'failed' => 1,
            'redirected' => 2
        ], $data['day'][date('Y-m-d')][date('Y-m-d H:')]);

        $this->assertEquals([
            'failed' => 1,
            'redirected' => 2
        ], $data['month'][date('Y-m')][date('Y-m-d')]);
    }

    public function testGetDay(): void
    {
        $count = Stats::count($this->_data());
        $this->assertTrue($count);

        $get = Stats::get('day');

        $this->assertEquals(date('j F Y'), $get['headline']);
        $this->assertEquals(24, count($get['labels']));

        $this->assertEquals(24, count($get['failed']));
        $this->assertEquals(1, $get['failed'][date('H')]);

        $this->assertEquals(24, count($get['redirected']));
        $this->assertEquals(2, $get['redirected'][date('H')]);
    }

    public function testGetWeek(): void
    {
        $count = Stats::count($this->_data());
        $this->assertTrue($count);

        $get = Stats::get('week');

        $this->assertEquals(Stats::headline(strtotime('Monday this week'), strtotime('Sunday this week')), $get['headline']);
        $this->assertEquals(7, count($get['labels']));

        $this->assertEquals(7, count($get['failed']));
        $this->assertEquals(1, $get['failed'][date('N')-1]);

        $this->assertEquals(7, count($get['redirected']));
        $this->assertEquals(2, $get['redirected'][date('N')-1]);
    }

    public function testGetMonth(): void
    {
        $count = Stats::count($this->_data());
        $this->assertTrue($count);

        $get = Stats::get('month');

        $this->assertEquals(date('F Y'), $get['headline']);
        $this->assertEquals(date('t'), count($get['labels']));

        $this->assertEquals(date('t'), count($get['failed']));
        $this->assertEquals(1, $get['failed'][date('d')-1]);

        $this->assertEquals(date('t'), count($get['redirected']));
        $this->assertEquals(2, $get['redirected'][date('d')-1]);
    }

    public function testHeadline(): void
    {
        $this->assertEquals(
            '29 January 1989',
            Stats::headline(
                strtotime('1989-01-29 09:30'),
                strtotime('1989-01-29 20:15')
            )
        );

        $this->assertEquals(
            'January 1989',
            Stats::headline(
                strtotime('1989-01-01 09:30'),
                strtotime('1989-01-31 20:15')
            )
        );

        $this->assertEquals(
            '1-29 January 1989',
            Stats::headline(
                strtotime('1989-01-01 09:30'),
                strtotime('1989-01-29 20:15')
            )
        );

        $this->assertEquals(
            '29 January - 29 July 1989',
            Stats::headline(
                strtotime('1989-01-29 09:30'),
                strtotime('1989-07-29 20:15')
            )
        );

        $this->assertEquals(
            '29 January 1989 - 29 January 2019',
            Stats::headline(
                strtotime('1989-01-29 09:30'),
                strtotime('2019-01-29 20:15')
            )
        );
    }
}
