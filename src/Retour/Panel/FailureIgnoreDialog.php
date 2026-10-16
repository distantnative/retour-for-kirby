<?php

namespace Kirby\Retour\Panel;

use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Retour\Retour;
use Kirby\Toolkit\HtmlString;
use Kirby\Toolkit\I18n;

/**
 * Adds the path of a failure (or a pattern based on it)
 * to the ignored paths and removes all matching failures
 */
class FailureIgnoreDialog extends DialogController
{
	public function __construct(
		protected string $path
	) {
		parent::__construct();
		$this->path = Id::toPath($path);
	}

	public function load(): FormDialog
	{
		return new FormDialog(
			fields: [
				'pattern' => [
					'type'     => 'text',
					'label'    => I18n::translate('retour.failures.path'),
					'before'   => Retour::instance()->site(),
					'counter'  => false,
					'required' => true,
					'help'     => HtmlString::translate('retour.failures.ignore.help', [
						'docs' => 'https://distantnative.com/retour-for-kirby/redirects#path'
					])
				]
			],
			value: [
				'pattern' => $this->path
			],
			submitButton: [
				'icon' => 'hidden',
				'text' => I18n::translate('retour.failures.ignore')
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

		$retour->log()->removeFailures(
			fn (string $path) => $ignores->matches($path, [$pattern])
		);

		return true;
	}
}
