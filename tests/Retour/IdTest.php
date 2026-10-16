<?php

namespace Kirby\Retour;

use Kirby\Retour\Panel\Id;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Id::class)]
class IdTest extends TestCase
{
	public function testToPath(): void
	{
		$this->assertSame('foo', Id::toPath('foo'));

		// slashes are replaced in the Panel
		// with the group separator and the
		// result is URL encoded
		$id = rawurlencode(str_replace('/', "\x1D", 'wp-admin/setup config.php'));
		$this->assertSame('wp-admin/setup config.php', Id::toPath($id));
	}
}
