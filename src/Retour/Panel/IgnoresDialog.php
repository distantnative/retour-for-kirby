<?php

namespace Kirby\Retour\Panel;

use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Retour\Ignores;
use Kirby\Retour\Retour;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\HtmlString;
use Kirby\Toolkit\Str;

/**
 * Lists the paths ignored via the Panel to remove
 * (or add) some and shows the ones from the config
 */
class IgnoresDialog extends DialogController
{
	/**
	 * Renders the paths as a comma-separated list of code elements
	 */
	protected function code(array $paths): HtmlString
	{
		return new HtmlString(implode(', ', array_map(
			fn (string $path) => '<code>' . Escape::html($path) . '</code>',
			$paths
		)));
	}

	public function fields(): array
	{
		$ignores = Retour::instance()->ignores();
		$fields  = [
			'patterns' => [
				'type'   => 'tags',
				'label'  => $this->i18n('retour.failures.ignores'),
				'help'   => $this->i18n('retour.failures.ignores.help'),
				'layout' => 'list',
				'theme'  => 'light'
			]
		];

		// offer the common paths as long as
		// not all of them have been added yet
		if (array_diff(Ignores::COMMON, $ignores->custom()) !== []) {
			$fields['common'] = [
				'type'  => 'toggle',
				'label' => $this->i18n('retour.failures.ignores.common'),
				'help'  => $this->i18nHtml('retour.failures.ignores.common.help', [
					'paths' => $this->code(['wp-login.php', 'wp-admin', 'xmlrpc.php', '.env'])
				])
			];
		}

		// patterns from the config can only be changed there
		$config = $ignores->config();

		if ($config !== []) {
			$fields['config'] = [
				'type'  => 'info',
				'label' => $this->i18n('retour.failures.ignores.config'),
				'text'  => $this->code($config)
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
				'text' => $this->i18n('save')
			]
		);
	}

	public function submit(): bool
	{
		$retour   = Retour::instance();
		$ignores  = $retour->ignores();
		$patterns = Str::split($this->request->get('patterns') ?? []);

		if (Str::toType($this->request->get('common') ?? false, 'bool') === true) {
			$patterns = [...$patterns, ...Ignores::COMMON];
		}

		$patterns = $ignores->set($patterns);

		// remove failures for newly added patterns
		$retour->log()->removeFailures(
			fn (string $path) => $ignores->matches($path, $patterns)
		);

		return true;
	}
}
