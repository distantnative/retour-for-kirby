<?php

namespace Kirby\Retour\Panel;

use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Retour\Retour;
use Kirby\Toolkit\A;

/**
 * Also removes all failures matching the ignored patterns
 */
class FailureIgnoreDialog extends DialogController
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

	public function load(): FormDialog
	{
		$field = match (count($this->paths)) {
			1 => [
				'type'     => 'text',
				'label'    => $this->i18n('retour.failures.path'),
				'before'   => Retour::instance()->site(),
				'counter'  => false,
				'required' => true,
				'help'     => $this->i18nHtml('retour.failures.ignore.help', [
					'docs' => 'https://distantnative.com/retour-for-kirby/redirects#path'
				])
			],
			default => (new IgnoresDialog())->fields()['patterns']
		};

		return new FormDialog(
			fields: [
				'patterns' => $field
			],
			value: [
				'patterns' => count($this->paths) === 1 ? $this->paths[0] : $this->paths
			],
			submitButton: [
				'icon' => 'muted',
				'text' => $this->i18n('retour.failures.ignore')
			]
		);
	}

	public function submit(): bool
	{
		$patterns = array_map(trim(...), A::wrap($this->request->get('patterns')));
		$patterns = array_filter($patterns, fn (string $pattern) => $pattern !== '');

		if ($patterns === []) {
			return false;
		}

		$retour  = Retour::instance();
		$ignores = $retour->ignores();
		$ignores->add(...$patterns);

		$retour->log()?->removeFailures(
			fn (string $path) => $ignores->matches($path, $patterns)
		);

		return true;
	}
}
