<?php

namespace Kirby\Retour\Panel;

use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog\RemoveDialog;
use Kirby\Retour\Retour;

/**
 * Removes the failures of a path after confirmation
 */
class FailureDeleteDialog extends DialogController
{
	public function __construct(
		protected string $path
	) {
		parent::__construct();
	}

	public static function factory(string $id): static
	{
		return new static(path: Id::decode($id));
	}

	public function load(): RemoveDialog
	{
		return new RemoveDialog(
			text: $this->i18n('field.structure.delete.confirm')
		);
	}

	public function submit(): bool
	{
		return Retour::instance()->log()?->remove($this->path) === true;
	}
}
