<?php

include_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/../index.php';

trait RetourTestCase
{
    protected function tearDown(): void
    {
        self::tearDownAfterClass();
    }

    public static function tearDownAfterClass(): void
    {
        distantnative\Retour\Retour::$instance = null;
        distantnative\Retour\Config::$file = null;
        distantnative\Retour\Config::$data = [];
    }
}
