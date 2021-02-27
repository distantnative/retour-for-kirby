<?php

namespace distantnative\Retour;

use Kirby\Api\Api;

require_once __DIR__ . '/mocks/ApiMock.php';

final class ApiTest extends TestCase
{

    protected static $router;

    public static function setUpBeforeClass(): void
    {
        $api = require dirname(__DIR__) . '/config/api.php';
        static::$router = new Api($api);
    }


    // public function testRoutesGet(): void
    // {
    //     TestCase::$mock = new RetourApiMock([
    //         [
    //             'from'   => 'foo',
    //             'to'     => 'bar',
    //             'status' => 200
    //         ],
    //         [
    //             'from'   => 'homer',
    //             'to'     => 'simpson',
    //             'status' => 'disabled'
    //         ]
    //     ]);

    //     $response = static::$router->call('retour/routes', 'GET', [
    //         'query' => [
    //             'from' => '2019-01-01',
    //             'to'   => '2019-12-31'
    //         ]
    //     ]);
    //     $this->assertSame([], $response);
    // }

    // public function testRoutesGet(): void
    // {
    //     TestCase::$mock = new RetourApiMock([
    //         [
    //             'from'   => 'foo',
    //             'to'     => 'bar',
    //             'status' => 200
    //         ],
    //         [
    //             'from'   => 'homer',
    //             'to'     => 'simpson',
    //             'status' => 'disabled'
    //         ]
    //     ]);

    //     $response = static::$router->call('retour/routes', 'POST', [
    //         'body' => [
    //             'from' => 'ashley',
    //             'to'   => 'simpson'
    //         ]
    //     ]);
    //     $this->assertSame([], $response);
    // }
}
