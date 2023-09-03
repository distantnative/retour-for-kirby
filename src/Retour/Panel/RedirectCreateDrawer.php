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
    public function load(): array
    {
        return [
            'component' => 'k-form-drawer',
            'props' => [
                'title'  => I18n::translate('add'),
                'icon'   => 'add',
                'fields' => $this->fields(),
                'value'  => $this->value()
            ]
        ];
    }

    public function submit(): bool
    {
        $redirects = $this->redirects();
        $data      = $this->data();
        $redirects->create($data);
        $redirects->save();
        return true;
    }
}
