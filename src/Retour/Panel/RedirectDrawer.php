<?php

namespace Kirby\Retour\Panel;

use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Http\Header;
use Kirby\Retour\Redirects;
use Kirby\Retour\Retour;
use Kirby\Toolkit\I18n;

/**
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */
abstract class RedirectDrawer
{
    protected function creator(): User|null
    {
        return  $this->kirby()->user();
    }

    protected function data(): array
    {
        return $this->kirby()->request()->get([
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
                'label'    => I18n::translate('retour.redirects.from'),
                'icon'     => 'bookmark',
                'before'   => $this->retour()->site(),
                'counter'  => false,
                'required' => true,
                'help'     => I18n::template('retour.redirects.from.help', [
                    'docs' => 'https://github.com/distantnative/retour-for-kirby#path'
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
                'help'     => I18n::template('retour.redirects.status.help', [
                    'docs' => 'https://github.com/distantnative/retour-for-kirby#status'
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
            ],
        ];
    }

    protected function kirby(): App
    {
        return App::instance();
    }

    protected function redirects(): Redirects
    {
        return $this->retour()->redirects();
    }

    protected function retour(): Retour
    {
        return Retour::instance();
    }

    protected function value(): array
    {
        $creator = $this->creator();

        return [
            'creator' => $creator ? [$creator->panel()->pickerData()] : []
        ];
    }
}
