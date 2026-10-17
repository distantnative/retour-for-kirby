<?php

namespace Kirby\Retour;

use Kirby\Exception\NotFoundException;
use Kirby\Http\Router;
use Kirby\Retour\Panel\Id;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Id::class)]
class IdTest extends TestCase
{
	public function testDecodeInvalid(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Invalid id');
		Id::decode('foo!');
	}

	public function testEncode(): void
	{
		$router = new Router([
			[
				'pattern' => 'retour/failures/(:any)/delete',
				'action'  => fn (string $id) => Id::decode($id)
			]
		]);

		// paths with slashes or characters that `(:any)` doesn't accept
		foreach (['foo', 'wp-admin/setup config.php', '~user/x', "it's!*(x)", 'blog/.*', 'äöü?a=b&c#d', ''] as $path) {
			$id = Id::encode($path);
			$this->assertMatchesRegularExpression('/^[a-zA-Z0-9_-]*$/', $id);
			$this->assertSame($path, Id::decode($id));

			if ($path !== '') {
				$this->assertSame($path, $router->call('retour/failures/' . $id . '/delete'));
			}
		}
	}

	public function testRedirect(): void
	{
		$redirects = Retour::instance($this->app([
			'distantnative.retour.config' => $this->tmp . '/retour.yml'
		]))->redirects();
		$redirects->create(['from' => 'foo/bar']);

		$this->assertSame($redirects->first(), Id::redirect(Id::encode('foo/bar')));

		$this->expectException(NotFoundException::class);
		Id::redirect(Id::encode('missing'));
	}
}
