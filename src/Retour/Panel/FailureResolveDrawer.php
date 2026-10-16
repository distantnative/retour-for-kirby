<?php

namespace Kirby\Retour\Panel;

use Kirby\Retour\Retour;

/**
 * Creates a redirect for a failure and marks the failure as resolved
 */
class FailureResolveDrawer extends RedirectCreateDrawer
{
	public function __construct(
		protected string $path
	) {
		parent::__construct();
	}

	public static function factory(string $id): static
	{
		return new static(path: Id::toPath($id));
	}

	public function submit(): array
	{
		parent::submit();

		Retour::instance()->log()?->resolve($this->path);

		return [
			'redirect' => 'retour/redirects'
		];
	}

	protected function title(): string
	{
		return $this->i18n('retour.failures.resolve');
	}

	protected function value(): array
	{
		return parent::value() + [
			'from' => $this->path
		];
	}
}
