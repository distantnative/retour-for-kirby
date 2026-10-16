<?php

namespace Kirby\Retour\Panel;

use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Retour\Retour;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\HtmlString;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

/**
 * Lists the paths ignored via the Panel to remove
 * (or add) some and shows the ones from the config
 */
class IgnoresDialog extends DialogController
{
	public function fields(): array
	{
		$fields = [
			'patterns' => [
				'type'   => 'tags',
				'label'  => I18n::translate('retour.failures.ignores'),
				'help'   => I18n::translate('retour.failures.ignores.help'),
				'layout' => 'list',
				'theme'  => 'light'
			]
		];

		// patterns from the config can only be changed there
		$config = Retour::instance()->ignores()->config();

		if ($config !== []) {
			$fields['config'] = [
				'type'  => 'info',
				'label' => I18n::translate('retour.failures.ignores.config'),
				'text'  => new HtmlString(implode(', ', array_map(
					fn (string $pattern) => '<code>' . Escape::html($pattern) . '</code>',
					$config
				)))
			];
		}

		return $fields;
	}

	public function load(): FormDialog
	{
		return new FormDialog(
			fields: $this->fields(),
			value: [
				'patterns' => Retour::instance()->ignores()->custom()
			],
			submitButton: [
				'text' => I18n::translate('save')
			]
		);
	}

	public function submit(): bool
	{
		$retour   = Retour::instance();
		$ignores  = $retour->ignores();
		$patterns = Str::split($this->request->get('patterns') ?? []);
		$patterns = $ignores->set($patterns);

		// remove failures for newly added patterns
		$retour->log()->removeFailures(
			fn (string $path) => $ignores->matches($path, $patterns)
		);

		return true;
	}
}
