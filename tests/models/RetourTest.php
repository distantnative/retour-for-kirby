<?php

namespace distantnative\Retour;

use Kirby\Cms\App;
use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

final class RetourTest extends TestCase
{

    protected function tearDown(): void
    {
        Retour::$instance = null;
    }

    public function testLog(): void
    {
        $fixture = __DIR__ . '/fixtures/test.sqlite';

        $app = new App([
            'roots'   => ['index' => '/dev/null'],
            'options' => [
                'distantnative.retour.database' => $fixture
            ]
        ]);

        $log = retour()->log();
        $this->assertInstanceOf('distantnative\Retour\Log', $log);
        $log->add(['path' => 'foo']);

        F::remove($fixture);

        Retour::$instance = null;
        $app = new App([
            'roots'   => ['index' => '/dev/null'],
            'options' => [
                'distantnative.retour.logs' => false,
                'distantnative.retour.database' => $fixture
            ]
        ]);
        $log = retour()->log();
        $this->assertInstanceOf('distantnative\Retour\LogDisabled', $log);
        $log->add(['path' => 'foo']);

        F::remove($fixture);
    }

    public function testMeta(): void
    {
        $app = new App([
            'roots'   => ['index' => '/dev/null'],
            'options' => [
                'distantnative.retour.logs' => false,
                'distantnative.retour.deleteAfter' => 6
            ]
        ]);

        $meta = Retour::meta();
        $this->assertSame(false, $meta['hasLog']);
        $this->assertSame(6, $meta['deleteAfter']);
        $this->assertTrue(count($meta['headers']) > 0);
    }

    public function testMetaDefaults(): void
    {
        $app = new App([
            'roots'   => ['index' => '/dev/null']
        ]);

        $meta = Retour::meta();
        $this->assertSame(true, $meta['hasLog']);
        $this->assertSame(false, $meta['deleteAfter']);
        $this->assertTrue(count($meta['headers']) > 0);
    }

    public function testRedirects(): void
    {
        $app = new App([
            'roots'   => ['index' => '/dev/null'],
            'options' => [
                'distantnative.retour.config' => __DIR__ . '/fixtures/sample.yml'
            ]
        ]);

        $redirects = retour()->redirects();
        $this->assertInstanceOf('distantnative\Retour\Redirects', $redirects);
        $this->assertSame(6, $redirects->count());
        $this->assertSame(6, count(Config::$data['redirects']));
    }

    public function testRedirectsEmpty(): void
    {
        $app = new App([
            'roots'   => ['index' => '/dev/null']
        ]);

        $redirects = retour()->redirects();
        $this->assertInstanceOf('distantnative\Retour\Redirects', $redirects);
        $this->assertSame([], $redirects->toArray());
    }
}
