<?php

namespace distantnative\Retour;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected $kirby;
    protected $plugin;
    protected $tmp = __DIR__ . '/tmp';

    public function setUp(): void
    {
        Dir::remove($this->tmp);

        $this->kirby = new App([
            'roots' => [
                'index'    => $this->tmp
            ]
        ]);

        $this->plugin = new Plugin($this->kirby);
    }

    public function tearDown(): void
    {
        Plugin::reset();
        Dir::remove($this->tmp);
    }
}
