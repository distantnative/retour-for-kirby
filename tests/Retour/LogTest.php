<?php

namespace Kirby\Retour;

use Closure;
use Kirby\Database\Database;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;

#[CoversClass(Log::class)]
class LogTest extends TestCase
{
	protected function log(
		string|Closure|null $file = null,
		array $options = []
	): Log {
		$file ??= __DIR__ . '/tmp/test.sqlite';
		$app    = $this->kirby->clone([
			'options' => array_merge($options, [
				'distantnative.retour.database' => $file
			])
		]);

		$retour = Retour::instance($app);
		return new Log($retour);
	}

	public function testAdd(): void
	{
		$log = $this->log();
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
	}

	public function testDatabase(): void
	{
		$log = $this->log();
		$this->assertInstanceOf(Database::class, $log->database());
	}

	#[DoesNotPerformAssertions]
	public function testDisabled(): void
	{
		$log = new LogDisabled();
		$log->add(['path' => 'foo']);
		$log->first();
		$log->redirect('foo', '2020-01-01', '2020-31-12');
	}

	public function testFails(): void
	{
		$log = $this->log();
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
	}

	public function testFile(): void
	{
		$file = __DIR__ . '/tmp/log/test.sqlite';
		$this->assertFalse(F::exists($file));
		$this->log(fn (): string => $file);
		$this->assertTrue(F::exists($file));
	}

	public function testFirstLast(): void
	{
		$log = $this->log();
		$this->assertSame([], $log->first());
		$this->assertSame([], $log->last());

		$log->add(['path' => 'foo']);

		$this->assertSame(1, $log->table()->fetch('array')->all()->count());
		$this->assertTrue(isset($log->first()['date']));
		$this->assertTrue(isset($log->last()['date']));
		$this->assertSame($log->first(), $log->last());
	}

	public function testFlush(): void
	{
		$log = $this->log();
		$this->assertSame(0, $log->table()->fetch('array')->all()->count());

		$log->add(['path' => 'foo']);
		$this->assertSame(1, $log->table()->fetch('array')->all()->count());

		$this->assertTrue($log->flush());
		$this->assertSame(0, $log->table()->fetch('array')->all()->count());
	}

	public function testFlushFailures(): void
	{
		$log = $this->log();

		// Add a 404 failure and a successful redirect entry
		$log->add(['path' => 'not-found']);
		$log->add(['path' => 'old', 'redirect' => 'old']);
		$this->assertSame(2, $log->table()->fetch('array')->all()->count());

		// Flushing failures should remove only the 404, not the redirect
		$this->assertTrue($log->flush('failures'));
		$this->assertSame(1, $log->table()->fetch('array')->all()->count());

		// The remaining entry must be the redirect, not the failure
		$remaining = $log->table()->fetch('array')->all()->first();
		$this->assertSame('old', $remaining['redirect']);
	}

	public function testPurge(): void
	{
		$file = __DIR__ . '/tmp/test.sqlite';
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
	}

	public function testPurgeDactivated(): void
	{
		$log = $this->log();
		$this->assertTrue($log->purge());
	}

	public function testRedirect(): void
	{
		$log = $this->log();
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
	}

	public function testRemove(): void
	{
		$log = $this->log();
		$this->assertTrue($log->remove('none'));

		$log->add(['path' => 'foo']);
		$log->add(['path' => 'foo']);
		$log->add(['path' => 'foo', 'referrer' => 'bar']);
		$log->add(['path' => 'foo', 'referrer' => 'homer simpson']);
		$log->add(['path' => 'bar']);

		$this->assertSame(5, $log->table()->fetch('array')->all()->count());
		$this->assertTrue($log->remove('foo'));
		$this->assertSame(1, $log->table()->fetch('array')->all()->count());
		$this->assertTrue($log->remove('bar'));
		$this->assertSame(0, $log->table()->fetch('array')->all()->count());
	}

	public function testResolve(): void
	{
		$log = $this->log();
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
	}

	public function testStats(): void
	{
		$file = __DIR__ . '/fixtures/sample.sqlite';
		$log  = $this->log($file);

		// Year
		$stats = $log->stats('year', '2019-01-01', '2019-12-31');
		$this->assertSame(12, count($stats));
		$this->assertSame('2019-12', $stats[11]['date']);
		$this->assertSame(235, $stats[11]['failed']);
		$this->assertSame(68, $stats[11]['resolved']);
		$this->assertSame(1021, $stats[11]['redirected']);

		// Month
		$stats = $log->stats('month', '2019-11-01', '2019-11-30');
		$this->assertSame(30, count($stats));
		$this->assertSame('2019-11-30', $stats[29]['date']);
		$this->assertSame(16, $stats[29]['failed']);
		$this->assertSame(3, $stats[29]['resolved']);
		$this->assertSame(33, $stats[29]['redirected']);

		// Week
		$stats = $log->stats('week', '2019-11-04', '2019-11-10');
		$this->assertSame(7, count($stats));
		$this->assertSame('2019-11-10', $stats[6]['date']);
		$this->assertSame(14, $stats[6]['failed']);
		$this->assertSame(3, $stats[6]['resolved']);
		$this->assertSame(30, $stats[6]['redirected']);

		// Day
		$stats = $log->stats('day', '2019-11-04', '2019-11-04');
		$this->assertSame(24, count($stats));
		$this->assertSame('2019-11-04 06', $stats[6]['date']);
		$this->assertSame(0, $stats[6]['failed']);
		$this->assertSame(0, $stats[6]['resolved']);
		$this->assertSame(4, $stats[6]['redirected']);
	}
}
