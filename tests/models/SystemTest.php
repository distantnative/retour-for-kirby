<?php

namespace distantnative\Retour;

use PHPUnit\Framework\TestCase;

class SystemTest extends TestCase
{
    public function testToArray(): void
    {
        $store = new System;
        $array = $store->toArray();

        $this->assertIsArray($array);

        $this->assertArrayHasKey('version', $array);
        $this->assertEquals('-', $array['version']);

        $this->assertArrayHasKey('site', $array);
        $this->assertEquals(site()->url(), $array['site']);

        $this->assertArrayHasKey('limit', $array);
        $this->assertArrayHasKey('headers', $array);
    }
}
