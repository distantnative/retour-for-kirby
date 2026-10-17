<?php

namespace Kirby\Retour\Panel;

use Kirby\Exception\NotFoundException;
use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog\RemoveDialog;
use Kirby\Retour\Redirect;
use Kirby\Retour\Retour;

/**
 * Removes a redirect after confirmation
 */
class RedirectDeleteDialog extends DialogController
{
	public function __construct(
		protected Redirect $redirect
	) {
		parent::__construct();
	}

	/**
	 * @throws NotFoundException If the redirect doesn't exist
	 */
	public static function factory(string $id): static
	{
		return new static(redirect: Id::toRedirect($id));
	}

	public function load(): RemoveDialog
	{
		return new RemoveDialog(
			text: $this->i18n('field.structure.delete.confirm')
		);
	}

	public function submit(): bool
	{
		return Retour::instance()->redirects()->delete($this->redirect->id());
	}
}
