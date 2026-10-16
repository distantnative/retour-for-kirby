<?php

namespace Kirby\Retour\Panel;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Retour\Retour;
use Kirby\Retour\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FailuresFlushDialog::class)]
class FailuresFlushDialogTest extends TestCase
{
	protected function dialog(array $body): FailuresFlushDialog
	{
		$app = $this->app([
			'distantnative.retour.database' => $this->tmp . '/log.sqlite'
		])->clone([
			'request' => [
				'body' => $body
			]
		]);

		$log = Retour::instance($app)->log();
		$log->add(['path' => 'foo']);
		$log->add(['path' => 'bar', 'redirect' => 'bar']);

		return new FailuresFlushDialog();
	}

	protected function records(): int
	{
		return Retour::instance()->log()->table()->fetch('array')->all()->count();
	}

	public function testSubmit(): void
	{
		$this->assertTrue($this->dialog(['mode' => 'failures'])->submit());
		$this->assertSame(1, $this->records());

		$this->assertTrue($this->dialog(['mode' => 'all'])->submit());
		$this->assertSame(0, $this->records());
	}

	public function testSubmitWithoutMode(): void
	{
		$dialog = $this->dialog([]);

		try {
			$dialog->submit();
			$this->fail('A missing mode must be rejected');
		} catch (InvalidArgumentException) {
			$this->assertSame(2, $this->records());
		}
	}
}
