<?php

namespace distantnative\Retour;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function testFixtures(): void
    {
        $dir = __DIR__ . '/fixtures';

        $file = $dir . '/retour.data';
        $this->assertEquals($file, Log::$file);

        $file = $dir . '/404.log';
        $this->assertEquals($dir, Logs::$dir);
        $this->assertEquals($file, Logs::$file);

        $file = $dir . '/{x}.stats';
        $this->assertEquals($file, Stats::$file);
    }
}
