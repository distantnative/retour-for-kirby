<?php

namespace Kirby\Retour\Panel;

use Kirby\Http\Header;
use Kirby\Panel\Controller\DrawerController;
use Kirby\Panel\Ui\Drawer\FormDrawer;
use Kirby\Panel\Ui\Item\UserItem;
use Kirby\Retour\Redirects;
use Kirby\Retour\Retour;

class RedirectCreateDrawer extends DrawerController
{
	/**
	 * UUID (or ID), which unlike the email address never changes
	 */
	protected function author(): string|null
	{
		$user = $this->kirby->user();
		return $user?->uuid()?->toString() ?? $user?->id();
	}

	protected function data(): array
	{
		return $this->request->get([
			'from',
			'to',
			'status',
			'priority',
			'comment'
		], '');
	}

	protected function fields(): array
	{
		$codes = array_map(fn ($code) => [
			'text'  => ltrim($code, '_') . ' - ' . Header::$codes[$code],
			'value' => ltrim($code, '_')
		], array_keys(Header::$codes));

		return [
			'from' => [
				'type'     => 'text',
				'label'    => $this->i18n('retour.redirects.from'),
				'icon'     => 'pin',
				'before'   => Retour::instance()->site(),
				'counter'  => false,
				'required' => true,
				'help'     => $this->i18nHtml('retour.redirects.from.help', [
					'docs' => 'https://distantnative.com/retour-for-kirby/redirects#path'
				])
			],
			'to' => [
				'type'     => 'link',
				'label'    => $this->i18n('retour.redirects.to'),
				'options'  => ['url', 'page', 'custom'],
				'help'     => $this->i18n('retour.redirects.to.help')
			],
			'status' => [
				'type'     => 'retour-status',
				'label'    => $this->i18n('status'),
				'options'  => $codes,
				'width'    => '1/2',
				'help'     => $this->i18nHtml('retour.redirects.status.help', [
					'docs' => 'https://distantnative.com/retour-for-kirby/redirects#status'
				])
			],
			'priority' => [
				'type'     => 'toggle',
				'label'    => $this->i18n('retour.redirects.priority'),
				'icon'     => 'bolt',
				'width'    => '1/2',
				'help'     => $this->i18n('retour.redirects.priority.help')
			],
			'comment' => [
				'type'     => 'textarea',
				'label'    => $this->i18n('retour.redirects.comment'),
				'icon'     => 'note',
				'buttons'  => false,
				'help'     => $this->i18n('retour.redirects.comment.help')
			],
			'creator' => [
				'type'     => 'retour-users',
				'label'    => $this->i18n('retour.redirects.creator'),
				'empty'    => $this->i18n('retour.redirects.creator.empty'),
			]
		];
	}

	public function load(): FormDrawer
	{
		return new FormDrawer(
			title: $this->title(),
			icon: 'add',
			fields: $this->fields(),
			value: $this->value()
		);
	}

	protected function redirects(): Redirects
	{
		return Retour::instance()->redirects();
	}

	public function submit(): bool|array
	{
		$this->redirects()->create([
			...$this->data(),
			'creator' => $this->author(),
		]);

		return true;
	}

	protected function title(): string
	{
		return $this->i18n('add');
	}

	/**
	 * Props for a `retour-users` field
	 */
	protected function user(string|null $id): array
	{
		if ($id !== null) {
			if ($user = $this->kirby->user($id)) {
				return [(new UserItem($user))->props()];
			}
		}

		return [];
	}

	protected function value(): array
	{
		return [
			'creator' => $this->user($this->author()),
			'status'  => Retour::instance()->option('status')
		];
	}
}
