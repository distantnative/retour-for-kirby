<?php

namespace Kirby\Retour\Panel;

use Kirby\Exception\NotFoundException;
use Kirby\Panel\Ui\Drawer\FormDrawer;
use Kirby\Retour\Redirect;
use Kirby\Retour\Retour;

class RedirectEditDrawer extends RedirectCreateDrawer
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
		return new static(redirect: Id::redirect($id));
	}

	protected function fields(): array
	{
		return [
			...parent::fields(),
			'modifier' => [
				'type'  => 'retour-users',
				'label' => $this->i18n('retour.redirects.modifier'),
				'empty' => $this->i18n('retour.redirects.creator.empty'),
			]
		];
	}

	public function load(): FormDrawer
	{
		$fields = $this->fields();

		// focus the field of the clicked table cell
		if ($column = $this->request->get('column')) {
			foreach (array_keys($fields) as $name) {
				$fields[$name]['autofocus'] = $name === $column;
			}
		}

		return new FormDrawer(
			fields: $fields,
			icon: 'redirect',
			title: $this->redirect->from(),
			value: $this->value(),
			options: [
				[
					'icon'   => 'trash',
					'title'  => $this->i18n('remove'),
					'dialog' => 'retour/redirects/' . Id::encode($this->redirect->id()) . '/delete'
				]
			]
		);
	}

	public function submit(): bool
	{
		Retour::instance()->redirects()->update($this->redirect->id(), [
			...$this->data(),
			'modifier' => $this->author(),
		]);

		return true;
	}

	protected function value(): array
	{
		$creator = $this->user($this->redirect->creator());

		return [
			...$this->redirect->toArray(),
			'creator'  => $creator,
			'modifier' => $this->user($this->redirect->modifier()) ?: $creator,
		];
	}
}
