<?php

namespace Kirby\Retour\Panel;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Retour\Retour;
use Kirby\Retour\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FailureIgnoreDialog::class)]
class FailureIgnoreDialogTest extends TestCase
{
	protected function dialog(string $path, array $body): FailureIgnoreDialog
	{
		$app = $this->app([
			'distantnative.retour.config'   => $this->tmp . '/retour.yml',
			'distantnative.retour.database' => $this->tmp . '/log.sqlite'
		])->clone([
			'request' => [
				'body' => $body
			]
		]);

		$log = Retour::instance($app)->log();
		$log->add(['path' => 'wp-login.php']);
		$log->add(['path' => 'wp-admin']);
		$log->add(['path' => 'foo(']);

		return FailureIgnoreDialog::factory(Id::encode($path));
	}

	protected function failures(): array
	{
		$failures = Retour::instance()->log()->fails('2000-01-01', '2099-12-31')->values();
		$paths    = array_column($failures, 'path');
		sort($paths);
		return $paths;
	}

	public function testSubmit(): void
	{
		$dialog = $this->dialog('wp-login.php', ['pattern' => 'wp-(:all)']);

		$this->assertTrue($dialog->submit());
		$this->assertSame(['wp-(:all)'], Retour::instance()->ignores()->custom());
		$this->assertSame(['foo('], $this->failures());
	}

	public function testSubmitInvalid(): void
	{
		$dialog = $this->dialog('foo(', ['pattern' => 'foo(']);

		try {
			$dialog->submit();
			$this->fail('Invalid patterns must be rejected');
		} catch (InvalidArgumentException) {
			$this->assertSame([], Retour::instance()->ignores()->custom());
			$this->assertSame(['foo(', 'wp-admin', 'wp-login.php'], $this->failures());
		}
	}
}
