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
class RedirectEditDrawer extends RedirectDrawer
{
    public function __construct(
        protected string $id
    )
    {
        $this->id = urldecode($id);
    }

    public function load(): array
    {
        // get redirect
        $redirects = $this->redirects();
        $redirect  = $redirects->get($this->id);
        $fields    = $this->fields();

        // set autofocus if specific column cell was passed
        if (
            ($field = $this->kirby()->request()->get('column')) &&
            isset($fields[$field]) === true
        ) {
            $fields[$field]['autofocus'] = true;
        }

        return [
            'component' => 'k-form-drawer',
            'props' => [
                'fields' => $fields,
                'icon'   => 'shuffle',
                'title'  => $redirect->from(),
                'value'  => $redirect->toArray(),
            ]
        ];
    }

    public function submit(): bool
    {
        $redirects = $this->redirects();
        $data      = $this->data();
        $redirects->update($this->id, $data);
        $redirects->save();
        return true;
    }
}
