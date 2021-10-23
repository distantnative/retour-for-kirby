<?php

namespace distantnative\Retour;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \distantnative\Retour\Log
 */
final class LogTest extends TestCase
{
    /**
     * @covers ::add
     * @covers ::table
     */
    public function testAdd(): void
    {
        $file = __DIR__ . '/fixtures/test.sqlite';
        $log  = $this->log($file);

        $this->assertSame(0, $log->table()->fetch('array')->all()->count());

        $log->add([
            'date'     => $date = '2020-01-01 07:30:00',
            'path'     => $path = 'foo',
            'redirect' => $redirect = 'foo'
        ]);
        $log->add([
            'date'     => '2020-01-31 16:30:00',
            'path'     => 'homer/simpson',
            'redirect' => '(:any)/simpson'
        ]);

        $results = $log->table()->fetch('array')->all();
        $this->assertSame(2, $results->count());
        $this->assertSame($date, $results->first()['date']);
        $this->assertSame($path, $results->first()['path']);
        $this->assertSame($redirect, $results->first()['redirect']);

        F::remove($file);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testDisabled(): void
    {
        $log = new LogDisabled();
        $log->add(['path' => 'foo']);
        $log->first();
        $log->redirect('foo', '2020-01-01', '2020-31-12');
    }

    /**
     * @covers ::fails
     */
    public function testFails(): void
    {
        $file = __DIR__ . '/fixtures/test.sqlite';
        $log  = $this->log($file);

        $this->assertSame([], $log->fails('2020-01-01', '2020-12-31'));

        $log->add([
            'date' => '2020-01-01 07:30:00',
            'path' => 'foo'
        ]);

        $log->add([
            'date' => $date = '2020-01-31 16:30:00',
            'path' => $path = 'foo'
        ]);

        $fail = $log->fails('2020-01-01', '2020-12-31')[0];
        $this->assertSame($path, $fail['path']);
        $this->assertSame($date, $fail['last']);
        $this->assertSame(2, $fail['hits']);

        F::remove($file);
    }

    /**
     * @covers ::__construct
     */
    public function testFile(): void
    {
        $file = __DIR__ . '/fixtures/log/test.sqlite';
        $this->assertFalse(F::exists($file));

        $log = $this->log(function () use ($file): string {
            return $file;
        });

        $this->assertTrue(F::exists($file));
        Dir::remove(dirname($file));
    }

    /**
     * @covers ::first
     * @covers ::last
     * @covers ::single
     */
    public function testFirstLast(): void
    {
        $file = __DIR__ . '/fixtures/test.sqlite';
        $log  = $this->log($file);

        $this->assertSame([], $log->first());
        $this->assertSame([], $log->last());

        $log->add(['path' => 'foo']);

        $this->assertSame(1, $log->table()->fetch('array')->all()->count());
        $this->assertTrue(isset($log->first()['date']));
        $this->assertTrue(isset($log->last()['date']));
        $this->assertSame($log->first(), $log->last());

        F::remove($file);
    }

    /**
     * @covers ::flush
     */
    public function testFlush(): void
    {
        $file = __DIR__ . '/fixtures/test.sqlite';
        $log  = $this->log($file);

        $this->assertSame(0, $log->table()->fetch('array')->all()->count());

        $log->add(['path' => 'foo']);
        $this->assertSame(1, $log->table()->fetch('array')->all()->count());

        $this->assertTrue($log->flush());
        $this->assertSame(0, $log->table()->fetch('array')->all()->count());

        F::remove($file);
    }

    /**
     * @covers ::purge
     */
    public function testPurge(): void
    {
        $file = __DIR__ . '/fixtures/test.sqlite';
        $log  = $this->log($file, [
            'distantnative.retour.deleteAfter' => 6
        ]);

        $this->assertSame(0, $log->table()->fetch('array')->all()->count());

        $log->add([
            'date' => date('Y-m-d H:i:s', strtotime('-12 month')),
            'path' => 'foo'
        ]);

        $log->add([
            'date' => date('Y-m-d H:i:s', strtotime('-3 month')),
            'path' => 'foo'
        ]);

        $this->assertSame(2, $log->table()->fetch('array')->all()->count());

        $this->assertTrue($log->purge());
        $this->assertSame(1, $log->table()->fetch('array')->all()->count());


        F::remove($file);
    }

    /**
     * @covers ::purge
     */
    public function testPurgeDactivated(): void
    {
        $file = __DIR__ . '/fixtures/test.sqlite';
        $log  = $this->log($file);
        $this->assertTrue($log->purge());
        F::remove($file);
    }

    /**
     * @covers ::redirect
     */
    public function testRedirect(): void
    {
        $file = __DIR__ . '/fixtures/test.sqlite';
        $log  = $this->log($file);

        $redirect = $log->redirect('foo', '2020-01-01', '2020-12-31');
        $this->assertSame(0, $redirect['hits']);
        $this->assertSame(null, $redirect['last']);

        $log->add([
            'date'     => '2020-01-01 07:30:00',
            'path'     => 'foo',
            'redirect' => 'foo'
        ]);

        $log->add([
            'date'     => $last = '2020-01-31 16:30:00',
            'path'     => 'foo',
            'redirect' => 'foo'
        ]);

        $log->add([
            'date' => '2020-01-31 16:30:00',
            'path' => 'foo'
        ]);

        $log->add([
            'date'     => '2021-01-31 16:30:00',
            'path'     => 'foo',
            'redirect' => 'foo'
        ]);

        $redirect = $log->redirect('foo', '2020-01-01', '2020-12-31');
        $this->assertSame($last, $redirect['last']);
        $this->assertSame(2, $redirect['hits']);

        F::remove($file);
    }

    /**
     * @covers ::remove
     */
    public function testRemove(): void
    {
        $file = __DIR__ . '/fixtures/test.sqlite';
        $log  = $this->log($file);

        $this->assertTrue($log->remove('none'));

        $log->add(['path' => 'foo']);
        $log->add(['path' => 'foo']);
        $log->add(['path' => 'foo', 'referrer' => 'bar']);
        $log->add(['path' => 'foo', 'referrer' => 'homer simpson']);
        $log->add(['path' => 'bar']);

        $this->assertSame(5, $log->table()->fetch('array')->all()->count());
        $this->assertTrue($log->remove('foo', 'bar'));
        $this->assertSame(4, $log->table()->fetch('array')->all()->count());
        $this->assertTrue($log->remove('foo'));
        $this->assertSame(2, $log->table()->fetch('array')->all()->count());

        F::remove($file);
    }

    /**
     * @covers ::resolve
     */
    public function testResolve(): void
    {
        $file = __DIR__ . '/fixtures/test.sqlite';
        $log  = $this->log($file);

        $log->add([
            'date' => '2020-01-01 07:30:00',
            'path' => 'foo'
        ]);

        $log->add([
            'date' => '2020-01-31 16:30:00',
            'path' => 'bar'
        ]);

        $this->assertSame(2, count($log->fails('2020-01-01', '2020-12-31')));
        $this->assertTrue($log->resolve('foo'));
        $this->assertSame(1, count($log->fails('2020-01-01', '2020-12-31')));

        F::remove($file);
    }

    /**
     * @covers ::stats
     */
    public function testStats(): void
    {
        $file = __DIR__ . '/fixtures/sample.sqlite';
        $log  = $this->log($file);

        // Year
        $stats = $log->stats('year', '2019-01-01', '2019-12-31');
        $this->assertSame(12, count($stats));
        $this->assertSame('2019-12-01', $stats[11]['date']);
        $this->assertSame(303, $stats[11]['failed']);
        $this->assertSame(68, $stats[11]['resolved']);
        $this->assertSame(1021, $stats[11]['redirected']);

        // Month
        $stats = $log->stats('month', '2019-11-01', '2019-11-30');
        $this->assertSame(30, count($stats));
        $this->assertSame('2019-11-30', $stats[29]['date']);
        $this->assertSame(19, $stats[29]['failed']);
        $this->assertSame(3, $stats[29]['resolved']);
        $this->assertSame(33, $stats[29]['redirected']);

        // Week
        $stats = $log->stats('week', '2019-11-04', '2019-11-10');
        $this->assertSame(7, count($stats));
        $this->assertSame('2019-11-10', $stats[6]['date']);
        $this->assertSame(17, $stats[6]['failed']);
        $this->assertSame(3, $stats[6]['resolved']);
        $this->assertSame(30, $stats[6]['redirected']);

        // Day
        $stats = $log->stats('day', '2019-11-04', '2019-11-04');
        $this->assertSame(24, count($stats));
        $this->assertSame('2019-11-04 06:00:00', $stats[6]['date']);
        $this->assertSame(0, $stats[6]['failed']);
        $this->assertSame(0, $stats[6]['resolved']);
        $this->assertSame(4, $stats[6]['redirected']);
    }

    protected function log($file, array $options = [])
    {
        $app  = new App([
            'roots'   => ['index' => '/dev/null'],
            'options' => array_merge($options, [
                'distantnative.retour.database' => $file
            ])
        ]);

        return new Log();
    }
}
