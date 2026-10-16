<?php

namespace Kirby\Retour;

use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Ignores::class)]
class IgnoresTest extends TestCase
{
	protected function ignores(array $options = []): Ignores
	{
		$app = $this->app([
			'distantnative.retour.config' => $this->tmp . '/retour.yml',
			...$options
		]);

		return Retour::instance($app)->ignores();
	}

	public function testAdd(): void
	{
		$ignores = $this->ignores();
		$this->assertSame(['foo'], $ignores->add('foo'));
		$this->assertSame(['foo', 'bar/(:all)'], $ignores->add(' bar/(:all) '));

		// no duplicates
		$this->assertSame(['foo', 'bar/(:all)'], $ignores->add('foo'));
	}

	public function testAddInvalid(): void
	{
		$ignores = $this->ignores();
		$ignores->add('foo');

		try {
			$ignores->add('foo(');
			$this->fail('Invalid patterns must be rejected');
		} catch (InvalidArgumentException $e) {
			$this->assertSame('Invalid pattern: foo(', $e->getMessage());
			$this->assertSame(['foo'], $ignores->custom());
			$this->assertSame(['foo'], Data::read($this->tmp . '/retour.yml')['ignore']);
		}
	}

	public function testAll(): void
	{
		$ignores = $this->ignores([
			'distantnative.retour.ignore' => ['foo']
		]);
		$ignores->add('bar');

		$this->assertSame(['foo', 'bar'], $ignores->all());
		$this->assertSame(['foo'], $ignores->config());
		$this->assertSame(['bar'], $ignores->custom());
	}

	public function testCommon(): void
	{
		$ignores = $this->ignores();

		foreach ([
			'wp-login.php',
			'wp-admin',
			'wp-admin/setup-config.php',
			'wp-content/plugins/foo/readme.txt',
			'wp-config.php.backup',
			'blog/wp-login.php',
			'wordpress/wp-admin/install.php',
			'xmlrpc.php',
			'blog/xmlrpc.php',
			'.env',
			'api/.env',
			'.git/config'
		] as $path) {
			$this->assertTrue($ignores->matches($path, Ignores::COMMON), $path);
		}

		foreach ([
			'blog',
			'blog/how-wordpress-works',
			'environment',
			'git/config'
		] as $path) {
			$this->assertFalse($ignores->matches($path, Ignores::COMMON), $path);
		}
	}

	public function testConfigSinglePattern(): void
	{
		$ignores = $this->ignores([
			'distantnative.retour.ignore' => 'wp-(:all)'
		]);

		$this->assertSame(['wp-(:all)'], $ignores->config());
		$this->assertTrue($ignores->matches('wp-admin'));
	}

	public function testCustomInConfigFile(): void
	{
		$retour = Retour::instance($this->app([
			'distantnative.retour.config' => $file = $this->tmp . '/retour.yml'
		]));

		$retour->redirects()->create(['from' => 'old', 'to' => 'new']);
		$retour->redirects()->save();
		$retour->ignores()->set(['foo', 'bar']);

		// stored next to the redirects
		$data = Data::read($file);
		$this->assertSame(['foo', 'bar'], $data['ignore']);
		$this->assertCount(1, $data['redirects']);

		// and read from there
		Retour::reset();
		$retour = Retour::instance($this->app([
			'distantnative.retour.config' => $file
		]));
		$this->assertSame(['foo', 'bar'], $retour->ignores()->custom());
		$this->assertTrue($retour->ignore('bar'));
	}

	public function testMatches(): void
	{
		$ignores = $this->ignores([
			'distantnative.retour.ignore' => ['foo']
		]);
		$ignores->add('wp-(:all)');

		$this->assertTrue($ignores->matches('foo'));
		$this->assertTrue($ignores->matches('wp-admin/setup.php'));
		$this->assertFalse($ignores->matches('bar'));

		// only the passed patterns
		$this->assertFalse($ignores->matches('foo', ['wp-(:all)']));
		$this->assertFalse($ignores->matches('foo', []));
	}

	public function testMatchesAlternatives(): void
	{
		$ignores = $this->ignores();

		$this->assertTrue($ignores->matches('foo', ['foo|bar']));
		$this->assertTrue($ignores->matches('bar', ['foo|bar']));
		$this->assertFalse($ignores->matches('foobaz', ['foo|bar']));
		$this->assertFalse($ignores->matches('xyzbar', ['foo|bar']));
	}

	public function testMatchesCaseInsensitive(): void
	{
		$ignores = $this->ignores();
		$this->assertTrue($ignores->matches('WP-Login.php', Ignores::COMMON));
	}

	public function testMatchesInvalidEntries(): void
	{
		$ignores = $this->ignores([
			'distantnative.retour.ignore' => ['wp-(:all)', null, '', ['foo']]
		]);

		$this->assertTrue($ignores->matches('wp-admin'));
		$this->assertFalse($ignores->matches('foo'));
		$this->assertFalse($ignores->matches(''));
	}

	public function testMatchesInvalidPattern(): void
	{
		$ignores = $this->ignores([
			'distantnative.retour.ignore' => ['foo(', 'bar']
		]);

		$this->assertFalse($ignores->matches('foo('));
		$this->assertFalse($ignores->matches('foo'));
		$this->assertTrue($ignores->matches('bar'));
	}

	public function testMatchesOptionalPlaceholder(): void
	{
		$ignores  = $this->ignores();
		$patterns = ['foo/(:any?)', 'bar'];

		$this->assertTrue($ignores->matches('foo', $patterns));
		$this->assertTrue($ignores->matches('foo/baz', $patterns));
		$this->assertTrue($ignores->matches('bar', $patterns));
		$this->assertFalse($ignores->matches('foobar', $patterns));
		$this->assertFalse($ignores->matches('bar/baz', $patterns));
	}

	public function testSet(): void
	{
		$ignores = $this->ignores();
		$ignores->add('foo');

		$this->assertSame(['bar', 'baz'], $ignores->set(['bar', '', ' baz ', 'bar']));
		$this->assertSame(['bar', 'baz'], $ignores->custom());
		$this->assertSame([], $ignores->set([]));
	}

	public function testSetInvalid(): void
	{
		$ignores = $this->ignores();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid pattern: (:any)/[');

		$ignores->set(['bar', '(:any)/[']);
	}
}
