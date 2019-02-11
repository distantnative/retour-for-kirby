<?php

namespace distantnative\Retour;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        $root = __DIR__ . '/fixtures';
        Retour::$dir = $root;
        Log::$file   = $root . '/retour.data';
        Logs::$file  = $root . '/404.log';
        Stats::$file = $root . '/{x}.stats';
    }

    public function testFixtures(): void
    {
        $dir = __DIR__ . '/fixtures';
        $this->assertEquals($dir, Retour::$dir);

        $file = $dir . '/retour.data';
        $this->assertEquals($file, Log::$file);

        $file = $dir . '/404.log';
        $this->assertEquals($file, Logs::$file);

        $file = $dir . '/{x}.stats';
        $this->assertEquals($file, Stats::$file);
    }
}
