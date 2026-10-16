<?php

namespace Kirby\Retour\Panel;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Retour\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TimespanDialog::class)]
class TimespanDialogTest extends TestCase
{
	protected function submit(array $body): array
	{
		$this->kirby->clone([
			'request' => [
				'body' => $body
			]
		]);

		return (new TimespanDialog())->submit();
	}

	public function testSubmit(): void
	{
		$result = $this->submit([
			'from' => '2024-01-10 00:00:00',
			'to'   => '2024-01-20 00:00:00'
		]);

		$this->assertArrayHasKey('redirect', $result);
		$this->assertSame(
			['from' => '2024-01-10', 'to' => '2024-01-20'],
			App::instance()->session()->get('retour')
		);
	}

	public function testSubmitInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$this->submit([
			'from' => 'x',
			'to'   => ['2024-01-10']
		]);
	}

	public function testSubmitToBeforeFrom(): void
	{
		$this->submit([
			'from' => '2024-01-20 00:00:00',
			'to'   => '2024-01-10 00:00:00'
		]);

		$this->assertSame(
			['from' => '2024-01-20', 'to' => '2024-01-20'],
			App::instance()->session()->get('retour')
		);
	}
}
