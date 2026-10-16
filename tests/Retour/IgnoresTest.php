<?php

namespace Kirby\Retour;

use Kirby\Data\Data;
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

	public function testSet(): void
	{
		$ignores = $this->ignores();
		$ignores->add('foo');

		$this->assertSame(['bar', 'baz'], $ignores->set(['bar', '', ' baz ', 'bar']));
		$this->assertSame(['bar', 'baz'], $ignores->custom());
		$this->assertSame([], $ignores->set([]));
	}
}
