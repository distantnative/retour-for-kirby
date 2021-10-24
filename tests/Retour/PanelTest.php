<?php

namespace distantnative\Retour;

/**
 * @coversDefaultClass \distantnative\Retour\Panel
 */
class PanelTest extends TestCase
{
    /**
     * @covers ::props
     */
    public function testProps(): void
    {
        // redirects
        $props = Panel::props('redirects');
        $this->assertSame('redirects', $props['tab']);
        $this->assertSame(3, count($props['tabs']));
        $this->assertSame([], $props['data']);

        // failures
        $props = Panel::props('failures');
        $this->assertSame('failures', $props['tab']);
        $this->assertSame(3, count($props['tabs']));
        $this->assertSame([], $props['data']);

        // system
        $props = Panel::props('system');
        $this->assertSame('system', $props['tab']);
        $this->assertSame(3, count($props['tabs']));
        $this->assertSame(0, $props['data']['redirects']);
        $this->assertSame(0, $props['data']['failures']);
        $this->assertSame('-', $props['data']['deleteAfter']);
    }

    /**
     * @covers ::props
     */
    public function testPropsNoLog(): void
    {
        $app = $this->kirby->clone([
            'options' => [
                'distantnative.retour.logs' => false
            ]
        ]);
        $this->plugin = Plugin::instance($app);

        $props = Panel::props('redirects');
        $this->assertSame('redirects', $props['tab']);
        $this->assertSame(1, count($props['tabs']));
    }

    // /**
    //  * @covers ::props
    //  */
    // public function testPropsWithData(): void
    // {
    //     $app = $this->kirby->clone([
    //         'options' => [
    //             'distantnative.retour.config' => __DIR__ . '/fixtures/sample.yml',
    //             'distantnative.retour.logs' => __DIR__ . '/fixtures/sample.sqlite'
    //         ]
    //     ]);
    //     $this->plugin = Plugin::instance($app);

    //     $_GET['from'] = '2021-01-01';
    //     $_GET['to']   = '2021-01-31';

    //     $props = Panel::props('system');
    //     $this->assertSame('system', $props['tab']);
    //     $this->assertSame(3, count($props['tabs']));
    //     $this->assertSame(4, $props['tabs'][0]['badge']);
    //     $this->assertSame(0, $props['tabs'][1]['badge']);
    //     $this->assertSame(0, $props['data']['redirects']);
    //     $this->assertSame(0, $props['data']['failures']);
    // }

    /**
     * @covers ::timespan
     */
    public function testTimespan(): void
    {
        $timespan = Panel::timespan($this->plugin);
        $this->assertSame(date('Y-m-01'), $timespan['from']);
        $this->assertSame(date('Y-m-t'), $timespan['to']);
        $this->assertSame('month', $timespan['unit']);
    }

    /**
     * @covers ::unit
     */
    public function testUnit(): void
    {
        // day
        $unit = Panel::unit(['from' => '2021-02-01', 'to' => '2021-02-01']);
        $this->assertSame('day', $unit);

        // month
        $unit = Panel::unit(['from' => '2021-02-01', 'to' => '2021-02-28']);
        $this->assertSame('month', $unit);

        // year
        $unit = Panel::unit(['from' => '2021-01-01', 'to' => '2021-12-31']);
        $this->assertSame('year', $unit);

        // months
        $unit = Panel::unit(['from' => '2021-01-01', 'to' => '2021-12-30']);
        $this->assertSame('months', $unit);
        $unit = Panel::unit(['from' => '2020-02-01', 'to' => '2021-02-28']);
        $this->assertSame('months', $unit);

        // days
        $unit = Panel::unit(['from' => '2021-01-01', 'to' => '2021-02-18']);
        $this->assertSame('days', $unit);
    }

    /**
     * @covers ::view
     */
    public function testView(): void
    {
        $view = Panel::view('redirects');

        $this->assertSame('k-retour-view', $view['component']);
        $this->assertSame('Routes', $view['title']);
        $this->assertSame('retour/redirects', $view['breadcrumb'][0]['link']);
        $this->assertIsArray($view['props']);
    }
}
