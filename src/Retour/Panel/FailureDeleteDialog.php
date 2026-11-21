<?php

namespace Kirby\Retour\Panel;

use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog\RemoveDialog;
use Kirby\Retour\Retour;

class FailureDeleteDialog extends DialogController
{
	public function __construct(
		protected array $paths
	) {
		parent::__construct();
	}

	/**
	 * @param $ids One or more ids, separated by `.`
	 */
	public static function factory(string $ids): static
	{
		return new static(paths: array_map(Id::decode(...), explode('.', $ids)));
	}

	public function load(): RemoveDialog
	{
		return new RemoveDialog(
			text: $this->i18n(match (count($this->paths)) {
				1       => 'field.structure.delete.confirm',
				default => 'field.structure.delete.confirm.selected'
			})
		);
	}

	public function submit(): bool
	{
		return Retour::instance()->log()?->remove(...$this->paths) === true;
	}
}
