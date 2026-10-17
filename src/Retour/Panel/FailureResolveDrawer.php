<?php

namespace Kirby\Retour\Panel;

use Kirby\Retour\Retour;

class FailureResolveDrawer extends RedirectCreateDrawer
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
