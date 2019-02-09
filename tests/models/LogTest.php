<?php

namespace distantnative\Retour;

use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{

    protected function setUp(): void
    {
        Log::$file = __DIR__ .'/../fixtures/404.log';
    }

    public function testClass(): void
    {
        $log = new Log;
        $this->assertInstanceOf('distantnative\Retour\Log', $log);
    }

}
