<?php

namespace Kirby\Retour\Panel;

use Kirby\Panel\Panel;

/**
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */
class FailureResolveDrawer extends RedirectCreateDrawer
{
    public function __construct(
        protected string $path
    ) {
        $this->path = urldecode($path);
    }

    protected function value(): array
    {
        return parent::value() + [
            'from' => str_replace("\x1D",'/', $this->path)
        ];
    }

    public function submit(): bool
    {
        $redirects = $this->redirects();
        $data      = $this->data();
        $redirects->create($data);
        $redirects->save();
        $log = $this->retour()->log();
        $log->resolve($this->path);

        Panel::go('retour/redirects');
        return true;
    }
}
