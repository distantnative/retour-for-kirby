<?php

namespace distantnative\Retour;

use Kirby\Cms\App;

final class RouteTest extends TestCase
{

    protected function _app(array $options): App
    {
        return new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug'  => 'projects',
                        'children' => [
                            [
                                'slug' => 'project-a'
                            ]
                        ]
                    ]
                ]
            ],
            'options' => $options
        ]);
    }

    public function testHasPriority(): void
    {
        $route = new Route([]);
        $this->assertFalse($route->hasPriority());

        $route = new Route([
            'priority' => false
        ]);
        $this->assertFalse($route->hasPriority());

        $route = new Route([
            'priority' => null
        ]);
        $this->assertFalse($route->hasPriority());

        $route = new Route([
            'priority' => true
        ]);
        $this->assertTrue($route->hasPriority());
    }

    public function testIsActive(): void
    {
        $route = new Route([]);
        $this->assertFalse($route->isActive());

        $route = new Route([
            'status' => null
        ]);
        $this->assertFalse($route->isActive());

        $route = new Route([
            'status' => 300
        ]);
        $this->assertTrue($route->isActive());
    }

    public function testStatus(): void
    {
        $route = new Route([]);
        $this->assertSame(null, $route->status());

        $route = new Route([
            'status' => 'disabled'
        ]);
        $this->assertSame(null, $route->status());

        $route = new Route([
            'status' => 300
        ]);
        $this->assertSame(300, $route->status());

        $route = new Route([
            'status' => '300'
        ]);
        $this->assertSame(300, $route->status());
    }

    public function testToArray(): void
    {
        $route = new Route([
            'from'   => 'foo',
            'to'     => 'bar',
            'status' => 'disabled'
        ]);
        $this->assertSame([
            'from'   => 'foo',
            'to'     => 'bar',
            'status' => null,
            'priority' => null,
            'comment' => null
        ], $route->toArray());
    }

    public function testToRuleDisabled(): void
    {
        $route = new Route([
            'from'   => 'foo',
            'to'     => 'bar',
            'status' => 'disabled'
        ]);
        $this->assertSame([], $route->toRule());
    }

    // public function testToRule(): void
    // {
    //     $this->_app([
    //         'distantnative.retour.logs' => false
    //     ]);

    //     $route = new Route([
    //         'from'   => $pattern = 'projects/project-a',
    //         'to'     => 'bar',
    //         'status' => 200
    //     ]);

    //     $rule = $route->toRule();
    //     $this->assertSame($pattern, $rule['pattern']);

    //     $reponse = $rule['action']();
    //     $this->assertInstanceOf('Kirby\\Cms\\Page', $reponse);
    // }
}
