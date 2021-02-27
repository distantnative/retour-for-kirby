<?php

namespace distantnative\Retour;

use PHPUnit\Framework\TestCase as BaseTest;

function retour() {
    return TestCase::$mock;
}

class TestCase extends BaseTest {

    public static $mock;

}
