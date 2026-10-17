<?php

namespace Kirby\Retour;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Redirects::class)]
class RedirectsTest extends TestCase
{
	protected function data(): array
	{
		return [
			['from' => 'foo', 'to' => 'bar', 'status' => 307],
			['from' => 'homer', 'to' => 'simpson', 'status' => 'disabled', 'priority' => true],
			['from' => 'berlin', 'to' => 'caracas', 'status' => 200]
		];
	}

	protected function expected(): array
	{
		return [
			[
				'from'     => 'foo',
				'to'       => 'bar',
				'status'   => 307,
				'priority' => false,
				'comment'  => null,
				'creator'  => null,
				'modifier' => null,
			],
			[
				'from'     => 'homer',
				'to'       => 'simpson',
				'status'   => null,
				'priority' => true,
				'comment'  => null,
				'creator'  => null,
				'modifier' => null,
			],
			[
				'from'     => 'berlin',
				'to'       => 'caracas',
				'status'   => 200,
				'priority' => false,
				'comment'  => null,
				'creator'  => null,
				'modifier' => null,
			]
		];
	}

	public function testCreate(): void
	{
		$redirects = Retour::instance()->redirects();
		$this->assertSame(0, $redirects->count());
		$redirects->create(['from' => 'foo']);
		$this->assertSame(1, $redirects->count());
		$this->assertSame('foo', $redirects->first()->from());
	}

	public function testCreateDuplicate(): void
	{
		$redirects = Retour::instance()->redirects();

		$this->assertSame(0, $redirects->count());
		$redirects->create(['from' => 'foo']);
		$this->assertSame(1, $redirects->count());

		$this->expectException(DuplicateException::class);
		$redirects->create(['from' => 'foo']);
	}

	public static function invalidProvider(): array
	{
		return [
			'pattern'      => [['from' => 'foo(', 'status' => 301], 'Invalid path pattern: foo('],
			'pattern #'    => [['from' => 'foo#(:any)'], 'Invalid path pattern: foo#(:any)'],
			'status text'  => [['from' => 'foo', 'status' => 'abc'], 'Invalid status code: 0'],
			'status 999'   => [['from' => 'foo', 'status' => '999'], 'Invalid status code: 999'],
			'status array' => [['from' => 'foo', 'status' => ['301']], 'Invalid status code: 1'],
		];
	}

	#[DataProvider('invalidProvider')]
	public function testCreateInvalid(array $data, string $message): void
	{
		$redirects = Retour::instance()->redirects();

		try {
			$redirects->create($data);
			$this->fail('Invalid redirects must be rejected');
		} catch (InvalidArgumentException $e) {
			$this->assertSame($message, $e->getMessage());
			$this->assertSame(0, $redirects->count());
		}
	}

	public function testCreatePlainTextPattern(): void
	{
		$redirects = Retour::instance()->redirects();

		// matched as plain text by Kirby's router
		foreach (['*.html', 'old/[draft', 'foo)'] as $from) {
			$redirects->create(['from' => $from, 'status' => 301]);
			$this->assertSame($from, $redirects->first()->from());
		}

		$this->assertSame(3, $redirects->count());
	}

	public function testCreateValidStatus(): void
	{
		$redirects = Retour::instance()->redirects();

		foreach (['301', 302, '', null, 'disabled', '410'] as $i => $status) {
			$redirects->create(['from' => 'foo-' . $i, 'status' => $status]);
		}

		$this->assertSame(6, $redirects->count());
	}

	public function testCreateWithoutValidParameters(): void
	{
		$redirects = Retour::instance()->redirects();
		$this->expectException(InvalidArgumentException::class);
		$redirects->create([]);
	}

	public function testFactory(): void
	{
		$retour    = Retour::instance();
		$redirects = Redirects::factory($retour, $this->data());

		$this->assertSame(3, $redirects->count());
		$this->assertInstanceOf(Redirect::class, $redirects->first());
	}

	public function testSave(): void
	{
		$file = $this->tmp . '/redirects.yml';
		$this->assertFalse(F::exists($file));


		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $file
			]
		]);

		$retour    = Retour::instance($app);
		$data      = $this->data();
		$expected  = $this->expected();
		$redirects = Redirects::factory($retour, $data);
		$config    = $retour->config();

		$redirects->save();
		$this->assertTrue(F::exists($file));
		$config->read();
		$this->assertSame($expected, $config->get('redirects'));

		F::remove($file);
		$this->assertFalse(F::exists($file));

		$new = [
			'from'     => 'auckland',
			'to'       => 'squamish',
			'status'   => null,
			'priority' => false,
			'comment'  => null,
			'creator'  => null,
			'modifier' => null,
		];
		$redirect = new Redirect($new);
		$redirects = $redirects->prepend($redirect);

		array_unshift($expected, $new);

		$redirects->save();
		$this->assertTrue(F::exists($file));
		$this->assertSame($expected, $config->get('redirects'));
	}

	public function testToArray(): void
	{
		$retour    = Retour::instance();
		$redirects = Redirects::factory($retour, $this->data());
		$expected  = $this->expected();

		$this->assertSame($expected, $redirects->toArray());
	}

	public function testToData(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.database' => $this->tmp . '/test.sqlite'
			]
		]);

		$retour    = Retour::instance($app);
		$redirects = Redirects::factory($retour, $this->data());
		$data      = $redirects->toData('2020-01-01', '2020-12-31');
		$this->assertSame(3, count($data));
		$this->assertSame(0, $data[0]['hits']);

		$retour->log()->add([
			'path'     => 'foo',
			'date'     => $last = '2020-01-31 14:30:15',
			'redirect' => 'foo'
		]);

		$data = $redirects->toData('2020-01-01', '2020-12-31');
		$this->assertSame(3, count($data));
		$this->assertSame(1, $data[0]['hits']);
		$this->assertSame($last, $data[0]['last']);
	}

	public function testToDataNoLog(): void
	{
		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.logs' => false
			]
		]);

		$retour    = Retour::instance($app);
		$redirects = Redirects::factory($retour, $this->data());
		$this->assertSame(
			$this->expected(),
			$redirects->toData('2020-01-01', '2020-12-31')
		);
	}

	public function testToRoutes(): void
	{
		$retour    = Retour::instance();
		$redirects = Redirects::factory($retour, $this->data());

		// without priority
		$routes = $redirects->toRoutes(false);
		$this->assertSame(2, count($routes));
		$this->assertSame('foo', $routes[0]['pattern']);

		// with priority
		$routes = $redirects->toRoutes(true);
		$this->assertSame(0, count($routes));
	}

	public function testUpdate(): void
	{
		$redirects = Retour::instance()->redirects();

		// creating new
		$this->assertSame(0, $redirects->count());
		$redirects->create(['from' => 'foo']);
		$this->assertSame(1, $redirects->count());
		$this->assertSame('foo', $redirects->first()->from());

		// updating existing, re-keyed by the new path
		$redirects->update('foo', ['from' => 'bar']);
		$this->assertSame(1, $redirects->count());
		$this->assertSame('bar', $redirects->first()->from());
		$this->assertSame(['bar'], $redirects->keys());
		$this->assertSame('bar', $redirects->get('bar')->from());
	}

	public function testUpdateDuplicate(): void
	{
		$redirects = Redirects::factory(Retour::instance(), $this->data());

		try {
			$redirects->update('foo', ['from' => 'homer']);
			$this->fail('Updating to an existing path must fail');
		} catch (DuplicateException) {
			$this->assertSame(['foo', 'homer', 'berlin'], $redirects->keys());
			$this->assertSame('bar', $redirects->get('foo')->to());
			$this->assertSame('simpson', $redirects->get('homer')->to());
		}
	}

	#[DataProvider('invalidProvider')]
	public function testUpdateInvalid(array $data, string $message): void
	{
		$redirects = Redirects::factory(Retour::instance(), $this->data());

		try {
			$redirects->update('foo', $data);
			$this->fail('Invalid redirects must be rejected');
		} catch (InvalidArgumentException $e) {
			$this->assertSame($message, $e->getMessage());
			$this->assertSame($this->expected(), $redirects->toArray());
		}
	}

	public function testUpdateKeepsPosition(): void
	{
		$redirects = Redirects::factory(Retour::instance(), $this->data());

		$redirects->update('homer', ['from' => 'marge', 'to' => 'simpson']);
		$this->assertSame(['foo', 'marge', 'berlin'], $redirects->keys());

		// same path with a Redirect object
		$redirects->update('marge', new Redirect(['from' => 'marge', 'to' => 'bouvier']));
		$this->assertSame(['foo', 'marge', 'berlin'], $redirects->keys());
		$this->assertSame('bouvier', $redirects->get('marge')->to());

		// the Redirect object as key
		$redirects->update(new Redirect(['from' => 'berlin', 'to' => 'tokyo']));
		$this->assertSame(['foo', 'marge', 'berlin'], $redirects->keys());
		$this->assertSame('tokyo', $redirects->get('berlin')->to());
	}

	public function testUpdateNotFound(): void
	{
		$redirects = Redirects::factory(Retour::instance(), $this->data());

		$this->expectException(NotFoundException::class);
		$redirects->update('missing', ['from' => 'missing']);
	}

	public function testUpdateNumericId(): void
	{
		$redirects = Redirects::factory(Retour::instance(), [
			['from' => '404'],
			['from' => 'foo']
		]);

		$redirects->update('404', ['from' => '410']);
		$this->assertSame(2, $redirects->count());
		$this->assertFalse($redirects->has('404'));
		$this->assertSame('410', $redirects->first()->from());
	}
}
