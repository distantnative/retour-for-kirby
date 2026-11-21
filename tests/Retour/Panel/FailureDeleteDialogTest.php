<?php

namespace Kirby\Retour\Panel;

use Kirby\Retour\Retour;
use Kirby\Retour\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FailureDeleteDialog::class)]
class FailureDeleteDialogTest extends TestCase
{
	protected function dialog(array $paths): FailureDeleteDialog
	{
		$app = $this->app([
			'distantnative.retour.database' => $this->tmp . '/log.sqlite'
		]);

		$log = Retour::instance($app)->log();
		$log->add(['path' => 'foo']);
		$log->add(['path' => 'foo', 'referrer' => 'https://example.com']);
		$log->add(['path' => 'bar']);
		$log->add(['path' => 'baz']);

		return FailureDeleteDialog::factory(implode('.', array_map(Id::encode(...), $paths)));
	}

	protected function failures(): array
	{
		$failures = Retour::instance()->log()->fails('2000-01-01', '2099-12-31')->values();
		return array_values(array_unique(array_column($failures, 'path')));
	}

	public function testLoad(): void
	{
		$this->assertSame(
			'Do you really want to delete this row?',
			$this->dialog(['foo'])->load()->props()['text']
		);

		$this->assertSame(
			'Do you really want to delete the selected entries?',
			$this->dialog(['foo', 'bar'])->load()->props()['text']
		);
	}

	public function testSubmit(): void
	{
		$this->assertTrue($this->dialog(['foo'])->submit());
		$this->assertEqualsCanonicalizing(['bar', 'baz'], $this->failures());
	}

	public function testSubmitSeveral(): void
	{
		$this->assertTrue($this->dialog(['foo', 'bar'])->submit());
		$this->assertSame(['baz'], $this->failures());
	}
}
