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

	public function testSubmitRemovesFailures(): void
	{
		$app = $this->app([
			'distantnative.retour.config'   => $this->tmp . '/retour.yml',
			'distantnative.retour.database' => $this->tmp . '/log.sqlite'
		])->clone([
			'request' => [
				'body' => [
					'patterns' => ['foo', 'bar'],
					'common'   => 'true'
				]
			]
		]);

		$retour = Retour::instance($app);
		$retour->ignores()->set(['foo']);

		$log = $retour->log();

		foreach (['foo', 'bar', 'wp-login.php', 'baz'] as $path) {
			$log->add(['path' => $path]);
		}

		$this->assertTrue((new IgnoresDialog())->submit());

		// only the failures of newly added patterns are removed,
		// the ones of saved patterns have been removed before
		$paths = array_column($log->fails('2000-01-01', '2099-12-31'), 'path');
		sort($paths);
		$this->assertSame(['baz', 'foo'], $paths);
	}
}
