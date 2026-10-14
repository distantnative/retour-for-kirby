<?php

namespace Kirby\Retour\Panel;

use Kirby\Http\Header;
use Kirby\Panel\Controller\DrawerController;
use Kirby\Panel\Ui\Drawer\FormDrawer;
use Kirby\Retour\Redirects;
use Kirby\Retour\Retour;
use Kirby\Toolkit\HtmlString;
use Kirby\Toolkit\I18n;

class RedirectCreateDrawer extends DrawerController
{
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

		$fields = [
			'from' => [
				'type'     => 'text',
				'label'    => I18n::translate('retour.redirects.from'),
				'icon'     => 'bookmark',
				'before'   => Retour::instance()->site(),
				'counter'  => false,
				'required' => true,
				'help'     => HtmlString::translate('retour.redirects.from.help', [
					'docs' => 'https://distantnative.com/retour-for-kirby/redirects#path'
				])
			],
			'to' => [
				'type'     => 'link',
				'label'    => I18n::translate('retour.redirects.to'),
				'options'  => ['url', 'page', 'custom'],
				'help'     => I18n::translate('retour.redirects.to.help')
			],
			'status' => [
				'type'     => 'retour-status',
				'label'    => I18n::translate('retour.redirects.status'),
				'options'  => $codes,
				'width'    => '1/2',
				'help'     => HtmlString::translate('retour.redirects.status.help', [
					'docs' => 'https://distantnative.com/retour-for-kirby/redirects#status'
				])
			],
			'priority' => [
				'type'     => 'toggle',
				'label'    => I18n::translate('retour.redirects.priority'),
				'icon'     => 'bolt',
				'width'    => '1/2',
				'help'     => I18n::translate('retour.redirects.priority.help')
			],
			'comment' => [
				'type'     => 'textarea',
				'label'    => I18n::translate('retour.redirects.comment'),
				'icon'     => 'chat',
				'buttons'  => false,
				'help'     => I18n::translate('retour.redirects.comment.help')
			],
			'creator' => [
				'type'     => 'users',
				'label'    => I18n::translate('retour.redirects.creator'),
				'empty'    => I18n::translate('retour.redirects.creator.empty'),
				'disabled' => true,
			]
		];

		if ($this instanceof RedirectEditDrawer) {
			$fields['modifier'] = [
				'type'     => 'users',
				'label'    => I18n::translate('retour.redirects.modifier'),
				'empty'    => I18n::translate('retour.redirects.creator.empty'),
				'disabled' => true,
			];
		}

		return $fields;
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
		$redirects = $this->redirects();
		$redirects->create([
			...$this->data(),
			'creator' => $this->kirby->user()?->email(),
		]);
		$redirects->save();
		return true;
	}

	protected function title(): string
	{
		return I18n::translate('add');
	}

	protected function value(): array
	{
		return [
			'creator' => [$this->kirby->user()?->panel()->pickerData()],
			'status'  => Retour::instance()->option('status')
		];
	}
}
