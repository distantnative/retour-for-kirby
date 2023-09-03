<?php

namespace Kirby\Retour\Panel;

use Kirby\Toolkit\I18n;

/**
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */
class RedirectCreateDrawer extends RedirectDrawer
{
    protected function data(): array
    {
        return parent::data() + [
            'creator' => $this->creator()->email(),
        ];
    }

    public function load(): array
    {
        return [
            'component' => 'k-form-drawer',
            'props' => [
                'title'  => $this->title(),
                'icon'   => 'add',
                'fields' => $this->fields(),
                'value'  => $this->value(),
            ]
        ];
    }

    public function submit(): bool|array
    {
        $redirects = $this->redirects();
        $data      = $this->data();
        $redirects->create($data);
        $redirects->save();
        return true;
    }

    protected function title(): string
    {
        return I18n::translate('add');
    }
}
