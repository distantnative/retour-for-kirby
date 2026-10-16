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

	public function testIps(): void
	{
		$this->assertSame([], $this->ignores()->ips());

		// single address
		$ignores = $this->ignores([
			'distantnative.retour.ignoreIps' => '192.0.2.1'
		]);

		$this->assertSame(['192.0.2.1'], $ignores->ips());
		$this->assertTrue($ignores->matchesIp('192.0.2.1'));
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

	public function testMatchesIp(): void
	{
		$ignores = $this->ignores([
			'distantnative.retour.ignoreIps' => [
				'192.0.2.1',
				'198.51.100.0/24',
				'2001:db8::1',
				'2001:db8:abcd::/48'
			]
		]);

		foreach ([
			'192.0.2.1',
			'198.51.100.0',
			'198.51.100.255',
			'2001:db8::1',
			'2001:0DB8:0000:0000:0000:0000:0000:0001',
			'2001:db8:abcd:12::1',
			// IPv4 address mapped to IPv6
			'::ffff:192.0.2.1'
		] as $ip) {
			$this->assertTrue($ignores->matchesIp($ip), $ip);
		}

		foreach ([
			'192.0.2.2',
			'198.51.101.0',
			'2001:db8::2',
			'2001:db8:abce::1',
			'::ffff:192.0.2.2',
			'foo',
			'',
			null
		] as $ip) {
			$this->assertFalse($ignores->matchesIp($ip), (string)$ip);
		}
	}

	public function testMatchesIpInvalidEntries(): void
	{
		$ignores = $this->ignores([
			'distantnative.retour.ignoreIps' => [
				null,
				'',
				'foo',
				['192.0.2.1'],
				'192.0.2.1/',
				'192.0.2.1/x',
				'192.0.2.1/-1',
				'192.0.2.1/33',
				'2001:db8::1/129',
				' 198.51.100.1 '
			]
		]);

		$this->assertFalse($ignores->matchesIp('192.0.2.1'));
		$this->assertFalse($ignores->matchesIp('2001:db8::1'));
		$this->assertTrue($ignores->matchesIp('198.51.100.1'));
	}

	public function testMatchesIpPrefixLength(): void
	{
		// not on a byte boundary
		$ignores = $this->ignores([
			'distantnative.retour.ignoreIps' => ['10.0.16.0/20', '2001:db8::/33']
		]);

		$this->assertFalse($ignores->matchesIp('10.0.15.255'));
		$this->assertTrue($ignores->matchesIp('10.0.16.0'));
		$this->assertTrue($ignores->matchesIp('10.0.31.255'));
		$this->assertFalse($ignores->matchesIp('10.0.32.0'));
		$this->assertTrue($ignores->matchesIp('2001:db8:7fff::1'));
		$this->assertFalse($ignores->matchesIp('2001:db8:8000::'));

		// all addresses of one IP version
		$ignores = $this->ignores([
			'distantnative.retour.ignoreIps' => ['0.0.0.0/0']
		]);

		$this->assertTrue($ignores->matchesIp('203.0.113.9'));
		$this->assertFalse($ignores->matchesIp('2001:db8::1'));

		$ignores = $this->ignores([
			'distantnative.retour.ignoreIps' => ['::/0']
		]);

		$this->assertTrue($ignores->matchesIp('2001:db8::1'));
		$this->assertFalse($ignores->matchesIp('203.0.113.9'));

		// single address
		$ignores = $this->ignores([
			'distantnative.retour.ignoreIps' => ['192.0.2.1/32']
		]);

		$this->assertTrue($ignores->matchesIp('192.0.2.1'));
		$this->assertFalse($ignores->matchesIp('192.0.2.0'));
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
