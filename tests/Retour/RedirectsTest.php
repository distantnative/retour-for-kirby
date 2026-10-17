<?php

namespace Kirby\Retour;

use Kirby\Data\Data;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
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
				'language' => null,
				'status'   => 307,
				'priority' => false,
				'comment'  => null,
				'creator'  => null,
				'modifier' => null,
			],
			[
				'from'     => 'homer',
				'to'       => 'simpson',
				'language' => null,
				'status'   => null,
				'priority' => true,
				'comment'  => null,
				'creator'  => null,
				'modifier' => null,
			],
			[
				'from'     => 'berlin',
				'to'       => 'caracas',
				'language' => null,
				'status'   => 200,
				'priority' => false,
				'comment'  => null,
				'creator'  => null,
				'modifier' => null,
			]
		];
	}

	/**
	 * Returns the redirects stored in a config file with the given data
	 */
	protected function redirects(array $redirects = []): Redirects
	{
		Data::write($file = $this->tmp . '/retour.yml', ['redirects' => $redirects]);

		return Retour::instance($this->app([
			'distantnative.retour.config' => $file
		]))->redirects();
	}

	/**
	 * Returns the paths of the redirects in the config file
	 */
	protected function stored(): array
	{
		return array_column(Data::read($this->tmp . '/retour.yml')['redirects'], 'from');
	}

	public function testCreate(): void
	{
		$redirects = $this->redirects($this->data());
		$redirect  = $redirects->create(['from' => 'new']);

		$this->assertSame('new', $redirect->from());
		$this->assertSame(['new', 'foo', 'homer', 'berlin'], $redirects->keys());
		$this->assertSame(['new', 'foo', 'homer', 'berlin'], $this->stored());
	}

	public function testCreateDuplicate(): void
	{
		$redirects = $this->redirects($this->data());

		try {
			$redirects->create(['from' => 'foo']);
			$this->fail('Creating an existing path must fail');
		} catch (DuplicateException) {
			$this->assertSame(['foo', 'homer', 'berlin'], $this->stored());
		}
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
		$redirects = $this->redirects();

		try {
			$redirects->create($data);
			$this->fail('Invalid redirects must be rejected');
		} catch (InvalidArgumentException $e) {
			$this->assertSame($message, $e->getMessage());
			$this->assertSame([], $this->stored());
		}
	}

	public function testCreateKeepsConcurrentChanges(): void
	{
		$redirects = $this->redirects([$this->data()[0]]);

		// another request adds a redirect in the meantime
		Data::write($this->tmp . '/retour.yml', ['redirects' => [$this->data()[0], $this->data()[1]]]);

		$redirects->create(['from' => 'new']);
		$this->assertSame(['new', 'foo', 'homer'], $redirects->keys());
		$this->assertSame(['new', 'foo', 'homer'], $this->stored());
	}

	public function testCreatePlainTextPattern(): void
	{
		$redirects = $this->redirects();

		// matched as plain text by Kirby's router
		foreach (['*.html', 'old/[draft', 'foo)'] as $from) {
			$this->assertSame($from, $redirects->create(['from' => $from, 'status' => 301])->from());
		}

		$this->assertSame(['foo)', 'old/[draft', '*.html'], $this->stored());
	}

	public function testCreateValidStatus(): void
	{
		$redirects = $this->redirects();

		foreach (['301', 302, '', null, 'disabled', '410'] as $i => $status) {
			$redirects->create(['from' => 'foo-' . $i, 'status' => $status]);
		}

		$this->assertSame(6, $redirects->count());
	}

	public function testCreateWithoutValidParameters(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->redirects()->create([]);
	}

	public function testDelete(): void
	{
		$redirects = $this->redirects($this->data());

		$this->assertTrue($redirects->delete('homer'));
		$this->assertSame(['foo', 'berlin'], $redirects->keys());
		$this->assertSame(['foo', 'berlin'], $this->stored());
	}

	public function testFactory(): void
	{
		$retour    = Retour::instance();
		$redirects = Redirects::factory($retour, $this->data());

		$this->assertSame(3, $redirects->count());
		$this->assertInstanceOf(Redirect::class, $redirects->first());
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
		$redirects = Redirects::factory(Retour::instance(), [
			['from' => 'blog/(:any)', 'status' => 301],
			['from' => '/foo/', 'status' => 301],
			['from' => 'bar', 'status' => 301],
			['from' => 'news/(:num)', 'status' => 'disabled'],
			['from' => 'baz', 'status' => 'disabled'],
			['from' => 'homer', 'status' => 301, 'priority' => true],
		]);

		$patterns = fn (string $path, bool $priority = false): array =>
			array_column($redirects->toRoutes($path, $priority), 'pattern');

		// all patterns, but plain paths only for the same path
		$this->assertSame(['blog/(:any)', 'foo'], $patterns('foo'));
		$this->assertSame(['blog/(:any)', 'foo'], $patterns('/foo/'));
		$this->assertSame(['blog/(:any)'], $patterns('Foo'));
		$this->assertSame(['blog/(:any)'], $patterns('missing'));

		// without disabled redirects
		$this->assertSame(['blog/(:any)'], $patterns('baz'));

		// with priority
		$this->assertSame(['homer'], $patterns('homer', true));
		$this->assertSame([], $patterns('foo', true));
	}

	public function testUpdate(): void
	{
		$redirects = $this->redirects([
			['from' => 'foo', 'to' => 'bar', 'comment' => 'note']
		]);

		// merged into the current redirect, re-keyed by the new path
		$redirect = $redirects->update('foo', ['from' => 'baz']);
		$this->assertSame('baz', $redirect->from());
		$this->assertSame('bar', $redirect->to());
		$this->assertSame('note', $redirect->comment());
		$this->assertSame(['baz'], $redirects->keys());
		$this->assertSame(['baz'], $this->stored());
	}

	public function testUpdateDuplicate(): void
	{
		$redirects = $this->redirects($this->data());

		try {
			$redirects->update('foo', ['from' => 'homer']);
			$this->fail('Updating to an existing path must fail');
		} catch (DuplicateException) {
			$this->assertSame(['foo', 'homer', 'berlin'], $redirects->keys());
			$this->assertSame('bar', $redirects->get('foo')->to());
			$this->assertSame(['foo', 'homer', 'berlin'], $this->stored());
		}
	}

	#[DataProvider('invalidProvider')]
	public function testUpdateInvalid(array $data, string $message): void
	{
		$redirects = $this->redirects($this->data());

		try {
			$redirects->update('foo', $data);
			$this->fail('Invalid redirects must be rejected');
		} catch (InvalidArgumentException $e) {
			$this->assertSame($message, $e->getMessage());
			$this->assertSame($this->data(), Data::read($this->tmp . '/retour.yml')['redirects']);
		}
	}

	public function testUpdateKeepsConcurrentChanges(): void
	{
		$redirects = $this->redirects([$this->data()[0]]);

		// another request adds a redirect in the meantime
		Data::write($this->tmp . '/retour.yml', ['redirects' => [$this->data()[0], $this->data()[1]]]);

		$redirects->update('foo', ['from' => 'baz']);
		$this->assertSame(['baz', 'homer'], $redirects->keys());
		$this->assertSame(['baz', 'homer'], $this->stored());
	}

	public function testUpdateKeepsPosition(): void
	{
		$redirects = $this->redirects($this->data());

		$redirects->update('homer', ['from' => 'marge']);
		$this->assertSame(['foo', 'marge', 'berlin'], $redirects->keys());

		$redirects->update('marge', ['to' => 'bouvier']);
		$this->assertSame(['foo', 'marge', 'berlin'], $this->stored());
		$this->assertSame('bouvier', $redirects->get('marge')->to());
	}

	public function testUpdateNotFound(): void
	{
		$this->expectException(NotFoundException::class);
		$this->redirects($this->data())->update('missing', ['from' => 'missing']);
	}

	public function testUpdateNumericId(): void
	{
		$redirects = $this->redirects([
			['from' => '404'],
			['from' => 'foo']
		]);

		$redirects->update('404', ['from' => '410']);
		$this->assertFalse($redirects->has('404'));
		$this->assertSame(['410', 'foo'], $this->stored());
	}
}
