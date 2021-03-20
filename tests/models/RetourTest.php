<?php

namespace distantnative\Retour;

use Kirby\Cms\App;
use Kirby\Toolkit\F;

use PHPUnit\Framework\TestCase;
use RetourTestCase;

/**
 * @coversDefaultClass \distantnative\Retour\Retour
 */
final class RetourTest extends TestCase
{
    use RetourTestCase;

    /**
     * @covers ::__construct
     * @covers ::log
     */
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
        $this->assertInstanceOf('distantnative\Retour\Log', retour()->log());

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

        F::remove($fixture);
    }

    /**
     * @covers ::meta
     */
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

    /**
     * @covers ::meta
     */
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

    /**
     * @covers ::__construct
     * @covers ::redirects
     */
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

    /**
     * @covers ::__construct
     * @covers ::redirects
     */
    public function testRedirectsEmpty(): void
    {
        $app = new App([
            'roots'   => ['index' => '/dev/null']
        ]);

        $redirects = retour()->redirects();
        $this->assertInstanceOf('distantnative\Retour\Redirects', $redirects);
        $this->assertSame([], $redirects->toArray());
    }

    /**
     * @covers ::instance
     */
    public function testSingleton(): void
    {
        $app = new App([
            'roots'   => ['index' => '/dev/null']
        ]);

        $this->assertInstanceOf('distantnative\Retour\Retour', Retour::instance());
        $this->assertSame(retour(), Retour::instance());
    }
}
