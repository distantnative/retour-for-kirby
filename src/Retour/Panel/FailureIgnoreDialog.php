<?php

namespace Kirby\Retour\Panel;

use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Retour\Retour;

/**
 * Also removes all failures matching the ignored pattern
 */
class FailureIgnoreDialog extends DialogController
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

	public function load(): FormDialog
	{
		return new FormDialog(
			fields: [
				'pattern' => [
					'type'     => 'text',
					'label'    => $this->i18n('retour.failures.path'),
					'before'   => Retour::instance()->site(),
					'counter'  => false,
					'required' => true,
					'help'     => $this->i18nHtml('retour.failures.ignore.help', [
						'docs' => 'https://distantnative.com/retour-for-kirby/redirects#path'
					])
				]
			],
			value: [
				'pattern' => $this->path
			],
			submitButton: [
				'icon' => 'muted',
				'text' => $this->i18n('retour.failures.ignore')
			]
		);
	}

	public function submit(): bool
	{
		$pattern = trim($this->request->get('pattern') ?? '');

		if ($pattern === '') {
			return false;
		}

		$retour  = Retour::instance();
		$ignores = $retour->ignores();
		$ignores->add($pattern);

		$retour->log()?->removeFailures(
			fn (string $path) => $ignores->matches($path, [$pattern])
		);

		return true;
	}
}
