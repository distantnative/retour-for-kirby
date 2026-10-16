<?php

namespace Kirby\Retour\Panel;

use Kirby\Retour\Retour;

class FailureResolveDrawer extends RedirectCreateDrawer
{
	public function __construct(
		protected string $path
	) {
		parent::__construct();
		$this->path = Id::toPath($path);
	}

	public function submit(): bool|array
	{
		$redirects = $this->redirects();
		$input     = $this->data();

		$redirects->create([
			'creator' => $this->kirby->user()?->email(),
			...$input
		]);

		$redirects->save();

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
