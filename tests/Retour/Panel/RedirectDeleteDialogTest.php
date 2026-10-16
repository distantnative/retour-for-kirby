<?php

namespace Kirby\Retour\Panel;

use Kirby\Data\Data;
use Kirby\Exception\NotFoundException;
use Kirby\Retour\Retour;
use Kirby\Retour\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RedirectDeleteDialog::class)]
class RedirectDeleteDialogTest extends TestCase
{
	protected function dialog(string $id): RedirectDeleteDialog
	{
		Data::write($this->tmp . '/retour.yml', [
			'redirects' => [
				['from' => 'foo', 'to' => 'bar'],
				['from' => 'homer/simpson', 'to' => 'marge']
			]
		]);

		Retour::instance($this->app([
			'distantnative.retour.config' => $this->tmp . '/retour.yml',
			'distantnative.retour.logs'   => false
		]));

		return new RedirectDeleteDialog(urlencode(str_replace('/', "\x1D", $id)));
	}

	protected function paths(): array
	{
		$data = Data::read($this->tmp . '/retour.yml');
		return array_column($data['redirects'], 'from');
	}

	public function testSubmit(): void
	{
		$this->assertTrue($this->dialog('homer/simpson')->submit());
		$this->assertSame(['foo'], $this->paths());
	}

	public function testSubmitNotFound(): void
	{
		$dialog = $this->dialog('missing');

		try {
			$dialog->submit();
			$this->fail('Removing a missing redirect must fail');
		} catch (NotFoundException) {
			$this->assertSame(['foo', 'homer/simpson'], $this->paths());
		}
	}
}
