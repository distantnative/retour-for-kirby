<?php

namespace Kirby\Retour;

use Kirby\Cms\App;
use Kirby\Data\Data;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class RoutesTest extends TestCase
{
	public function testExtension(): void
	{
		$extension = App::plugin('distantnative/retour')->extends()['routes'];

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $this->tmp . '/test.yml',
				'distantnative.retour.logs'   => false
			]
		]);

		$this->assertSame(0, count($extension()));

		$retour = Retour::instance($app);
		$retour->redirects()->prepend(new Redirect([
			'from' => 'foo',
			'to'   => 'bar'
		]));

		$this->assertSame(0, count($extension()));

		$retour->redirects()->prepend(new Redirect([
			'from'     => 'foo',
			'to'       => 'bar',
			'priority' => true,
			'status'   => 307
		]));

		$this->assertSame(1, count($extension()));
	}

	public function testRenderRedirects(): void
	{
		// plugin routes are registered when the app is created
		Data::write($file = $this->tmp . '/retour.yml', [
			'redirects' => [
				['from' => 'priority', 'to' => 'target', 'status' => 301, 'priority' => true],
				['from' => 'fallback', 'to' => 'target', 'status' => 308]
			]
		]);

		Retour::reset();

		$app = $this->kirby->clone([
			'options' => [
				'distantnative.retour.config' => $file,
				'distantnative.retour.logs'   => false
			],
			'urls' => [
				'index' => 'https://example.com'
			],
			'site' => [
				'children' => [
					['slug' => 'target']
				]
			]
		]);

		// priority redirects are plugin routes, the others
		// are resolved in the `route:after` hook
		foreach (['priority' => 301, 'fallback' => 308] as $path => $code) {
			$response = $app->render($path, 'GET');
			$this->assertSame($code, $response->code(), $path);
			$this->assertSame('https://example.com/target', $response->header('Location'), $path);
		}
	}
}
