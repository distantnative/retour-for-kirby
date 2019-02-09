<?php

namespace distantnative\Retour;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{

    protected static $fixture;

    protected function _file(): string
    {
        return kirby()->root('site') . static::$fixture;
    }

}
