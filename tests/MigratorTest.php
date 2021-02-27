<?php

namespace distantnative\Retour;

final class MigratorTest extends TestCase
{
    public function testHasUpgrades(): void
    {
        $migration = new Migrator(
            '2.3.2',
            [ '3.0.0' => function () {} ]
        );

        $this->assertTrue($migration->hasUpgrades());

        $migration = new Migrator(
            '2.3.2',
            [
                '3.0.0' => function () {},
                '3.0.1' => function () {}
            ]
        );

        $this->assertTrue($migration->hasUpgrades());

        $migration = new Migrator(
            '3.0.0',
            [ '3.0.0' => function () {} ]
        );

        $this->assertFalse($migration->hasUpgrades());

        $migration = new Migrator(
            '3.0.1',
            [ '3.0.0' => function () {} ]
        );

        $this->assertFalse($migration->hasUpgrades());
    }

    public function testLatest(): void
    {
        $migration = new Migrator(
            '2.3.2',
            [ '3.0.0' => function () {} ]
        );

        $this->assertSame('3.0.0', $migration->latest());

        $migration = new Migrator(
            '2.3.2',
            [
                '3.0.0' => function () {},
                '3.0.1' => function () {}
            ]
        );

        $this->assertSame('3.0.1', $migration->latest());
    }

    public function testRunNoUpgrades(): void
    {
        $migration = new Migrator(
            '3.0.0',
            [ '3.0.0' => function () {} ]
        );

        $this->assertFalse($migration->run());

        $migration = new Migrator(
            '3.0.1',
            [ '3.0.0' => function () {} ]
        );

        $this->assertFalse($migration->run());
    }

    public function testRun(): void
    {
        $a = 0;
        $b = 0;

        $migration = new Migrator(
            '2.3.2',
            [
                '3.0.0' => function () use (&$a) {
                    $a++;
                },
                '3.0.1' => function () use (&$b) {
                    $b++;
                }
            ]
        );

        $this->assertSame('3.0.1', $migration->run());
        $this->assertSame(1, $a);
        $this->assertSame(1, $b);
    }
}
