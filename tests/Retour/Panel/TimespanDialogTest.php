<?php

namespace Kirby\Retour\Panel;

use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Retour\Retour;
use Kirby\Retour\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TimespanDialog::class)]
class TimespanDialogTest extends TestCase
{
	protected function dialog(string $from, string $to): FormDialog
	{
		$app = $this->app([
			'distantnative.retour.database' => $this->tmp . '/log.sqlite'
		])->clone([
			'request' => [
				'query' => ['from' => $from, 'to' => $to]
			]
		]);

		Retour::instance($app)->log()->add(['date' => '2023-03-15 10:00:00', 'path' => 'foo']);

		return (new TimespanDialog())->load();
	}

	public function testLoad(): void
	{
		$dialog = $this->dialog('2023-04-01', '2023-04-30');

		$this->assertSame('2023-03-15', $dialog->fields['from']['min']);
		$this->assertSame(date('Y-m-d'), $dialog->fields['to']['max']);
		$this->assertSame(['from' => '2023-04-01', 'to' => '2023-04-30'], $dialog->value);
	}

	public function testLoadBeyondLimits(): void
	{
		// e.g. the year of the first record or the current month
		$dialog = $this->dialog('2023-01-01', date('Y-m-d', strtotime('+1 month')));

		$this->assertSame([
			'from' => '2023-03-15',
			'to'   => date('Y-m-d')
		], $dialog->value);
	}
}
