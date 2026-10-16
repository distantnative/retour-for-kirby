<?php

namespace Kirby\Retour;

use Kirby\Cms\App;
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
}
