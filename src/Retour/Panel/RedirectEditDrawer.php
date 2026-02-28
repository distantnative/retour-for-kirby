<?php

namespace Kirby\Retour\Panel;

use Kirby\Exception\NotFoundException;
use Kirby\Retour\Redirect;
use Kirby\Toolkit\I18n;

class RedirectEditDrawer extends RedirectCreateDrawer
{
	public function __construct(
		protected string $id
	) {
		$this->id = urldecode($id);
	}

	public function load(): array
	{
		$fields = $this->fields();

		// set autofocus if specific column cell was passed
		if ($column = $this->kirby()->request()->get('column')) {
			foreach (array_keys($fields) as $name) {
				$fields[$name]['autofocus'] = $name === $column;
			}
		}

		return [
			'component' => 'k-form-drawer',
			'props' => [
				'fields'  => $fields,
				'icon'    => 'shuffle',
				'title'   => $this->redirect()->from(),
				'value'   => $this->value(),
				'options' => [
					[
						'icon'   => 'trash',
						'title'  => I18n::translate('remove'),
						'dialog' => 'retour/redirects/' . urlencode($this->id) . '/delete'
					]
				]
			]
		];
	}

	protected function redirect(): Redirect
	{
		return $this->redirects()->get($this->id) ?? throw new NotFoundException('Redirect not found');
	}

	public function submit(): bool
	{
		$redirects = $this->redirects();
		$data      = $this->data();
		$redirect  = new Redirect([
			...$data,
			'creator'  => $this->redirect()->creator(),
			'modifier' => $this->kirby()->user()?->email(),
		]);

		$redirects->set($this->id, $redirect);
		$redirects->save();
		return true;
	}

	protected function userField(string $field): array
	{
		if ($user = $this->redirect()->$field()) {
			if ($user = $this->kirby()->user($user)) {
				return [$user->panel()->pickerData()];
			}
		}

		return [];
	}

	protected function value(): array
	{
		$creator  = $this->userField('creator');
		$modifier = $this->userField('modifier');

		if ($modifier === []) {
			$modifier = $creator;
		}

		return [
			...$this->redirect()->toArray(),
			'creator'  => $creator,
			'modifier' => $modifier,
		];
	}
}
