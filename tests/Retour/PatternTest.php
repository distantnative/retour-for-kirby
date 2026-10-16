<?php

namespace Kirby\Retour;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Pattern::class)]
class PatternTest extends TestCase
{
	public function testIsLiteral(): void
	{
		$this->assertTrue(Pattern::isLiteral('foo/bar'));
		$this->assertTrue(Pattern::isLiteral('*.html'));
		$this->assertTrue(Pattern::isLiteral('foo)'));

		$this->assertFalse(Pattern::isLiteral('foo/(:any)'));
		$this->assertFalse(Pattern::isLiteral('foo('));
	}

	public function testIsValid(): void
	{
		$this->assertTrue(Pattern::isValid('foo'));
		$this->assertTrue(Pattern::isValid('foo/(:any)'));
		$this->assertTrue(Pattern::isValid('foo/(:all?)'));
		$this->assertTrue(Pattern::isValid('blog/(:num)/([a-z]+)'));

		$this->assertFalse(Pattern::isValid('foo('));
		$this->assertFalse(Pattern::isValid('foo)'));
		$this->assertFalse(Pattern::isValid('foo/['));
		$this->assertFalse(Pattern::isValid('foo#bar'));
	}

	public function testMatches(): void
	{
		$this->assertTrue(Pattern::matches('foo/(:any)', 'foo/bar'));
		$this->assertTrue(Pattern::matches('/foo/', 'foo'));
		$this->assertFalse(Pattern::matches('foo/(:any)', 'foo/bar/baz'));

		// flags
		$this->assertFalse(Pattern::matches('foo', 'FOO'));
		$this->assertTrue(Pattern::matches('foo', 'FOO', 'iu'));

		// alternatives are anchored as a whole
		$this->assertTrue(Pattern::matches('foo|bar', 'bar'));
		$this->assertFalse(Pattern::matches('foo|bar', 'foobaz'));

		// invalid patterns never match
		$this->assertFalse(Pattern::matches('foo(', 'foo('));
	}

	public function testRegex(): void
	{
		$this->assertSame('#^(?:foo/bar)$#u', Pattern::regex('/foo/bar/'));
		$this->assertSame('#^(?:foo/(.*))$#iu', Pattern::regex('foo/(:all)', 'iu'));
		$this->assertSame('#^(?:foo(?:/([a-zA-Z]+))?)$#u', Pattern::regex('foo/(:alpha?)'));
	}
}
