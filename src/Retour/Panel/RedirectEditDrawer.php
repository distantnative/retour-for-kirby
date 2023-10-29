<?php

namespace Kirby\Retour\Panel;

use Kirby\Cms\User;
use Kirby\Retour\Redirect;
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

    protected function creator(): User|null
    {
        $creator = $this->redirect()->creator();
        return $creator ? $this->kirby()->user($creator) : null;
    }

    public function load(): array
    {
        $fields = $this->fields();

        // set autofocus if specific column cell was passed
        if ($column = $this->kirby()->request()->get('column')) {
            foreach ($fields as $name => $field) {
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
                        'dialog' => 'retour/redirects/' . urlencode($this->id) .'/delete'
                    ]
                ]
            ]
        ];
    }

    protected function redirect(): Redirect
    {
        return $this->redirects()->get($this->id);
    }

    public function submit(): bool
    {
        $redirects = $this->redirects();
        $data      = $this->data();
        $redirects->update($this->id, $data);
        $redirects->save();
        return true;
    }

    protected function value(): array
    {
        return array_merge(
            $this->redirect()->toArray(),
            parent::value()
        );
    }
}
