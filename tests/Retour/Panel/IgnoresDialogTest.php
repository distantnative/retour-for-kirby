<?php

namespace Kirby\Retour\Panel;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Retour\Ignores;
use Kirby\Retour\Retour;
use Kirby\Retour\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IgnoresDialog::class)]
class IgnoresDialogTest extends TestCase
{
	protected function dialog(array $body): IgnoresDialog
	{
		$app = $this->app([
			'distantnative.retour.config' => $this->tmp . '/retour.yml',
			'distantnative.retour.logs'   => false
		])->clone([
			'request' => [
				'body' => $body
			]
		]);

		Retour::instance($app)->ignores()->set(['foo']);

		return new IgnoresDialog();
	}

	public function testSubmit(): void
	{
		$dialog = $this->dialog([
			'patterns' => ['bar', 'baz/(:any)'],
			'common'   => 'true'
		]);

		$this->assertTrue($dialog->submit());
		$this->assertSame(
			['bar', 'baz/(:any)', ...Ignores::COMMON],
			Retour::instance()->ignores()->custom()
		);
	}

	public function testSubmitInvalid(): void
	{
		$dialog = $this->dialog([
			'patterns' => ['bar', 'baz/(']
		]);

		try {
			$dialog->submit();
			$this->fail('Invalid patterns must be rejected');
		} catch (InvalidArgumentException $e) {
			$this->assertSame('Invalid pattern: baz/(', $e->getMessage());
			$this->assertSame(['foo'], Retour::instance()->ignores()->custom());
		}
	}
}
