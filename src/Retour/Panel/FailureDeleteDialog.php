<?php

namespace Kirby\Retour\Panel;

use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog\RemoveDialog;
use Kirby\Retour\Retour;

/**
 * Removes all log entries of a failure after confirmation
 */
class FailureDeleteDialog extends DialogController
{
	public function __construct(
		protected string $path
	) {
		parent::__construct();
		$this->path = Id::toPath($path);
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
