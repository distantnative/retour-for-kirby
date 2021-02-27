<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

class RetourMigrationsMock
{
    protected $config;
    protected $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function config() {
        return $this->config = $this->config ?? new Config($this->file);
    }
}

final class MigrationsTest extends TestCase
{

    protected static $migrations;
    protected $fixture = __DIR__ . '/fixtures/migration.yml';

    public static function setUpBeforeClass(): void
    {
        static::$migrations = require dirname(__DIR__) . '/config/migrations.php';
    }

    protected function tearDown(): void
    {
        F::remove($this->fixture);
    }

    protected function _mock($data)
    {
        Data::write($this->fixture, $data, 'yaml');
        return new RetourMigrationsMock($this->fixture);
    }

    public function test300(): void
    {
        $retour = $this->_mock($input = [
            [
                'from' => 'test',
                'to'   => 'home'
            ],
            [
                'from' => 'homer',
                'to'   => 'simpson'
            ]
        ]);

        $migration = static::$migrations['3.0.0']->bindTo($retour);

        $this->assertSame($input, $retour->config()->data());
        $migration();
        $this->assertSame([
            'routes' => [
                [
                    'from' => 'test',
                    'to'   => 'home',
                    'priority' => false
                ],
                [
                    'from' => 'homer',
                    'to'   => 'simpson',
                    'priority' => false
                ]
            ]
        ], $retour->config()->data());
    }
}
