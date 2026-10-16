<?php

namespace Kirby\Retour\Panel;

use Kirby\Exception\NotFoundException;
use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog\RemoveDialog;
use Kirby\Retour\Retour;

/**
 * Removes a redirect after confirmation
 */
class RedirectDeleteDialog extends DialogController
{
	public function __construct(
		protected string $id
	) {
		parent::__construct();
		$this->id = urldecode($id);
	}

	public function load(): RemoveDialog
	{
		return new RemoveDialog(
			text: $this->i18n('field.structure.delete.confirm')
		);
	}

	public function submit(): bool
	{
		$redirects = Retour::instance()->redirects();

		if ($redirects->has($this->id) === false) {
			throw new NotFoundException(
				message: 'Redirect not found'
			);
		}

		$redirects->remove($this->id);
		$redirects->save();

		return true;
	}
}
