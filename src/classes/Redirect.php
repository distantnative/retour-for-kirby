<?php

namespace distantnative\Retour;

use Kirby\Http\Header;
use Kirby\Http\Url;
use Kirby\Toolkit\Obj;
use Kirby\Toolkit\Str;

/**
 * Redirect
 * Single redirect with its properties
 *
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */

class Redirect extends Obj
{

    /**
     * Returns whether the route is enabled
     * with status code
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status() !== null;
    }

    /**
     * Returns whether the route takes priority
     * over actual pages
     *
     * @return bool
     */
    public function priority(): bool
    {
        return Str::toType($this->priority ?? 'false', 'bool') === true;
    }

    /**
     * Returns the integer HTTP status code
     * @return int|null
     */
    public function status(): ?int
    {
        if (in_array($this->status ?? null, [null, 'disabled']) == true) {
            return null;
        }

        return (int)$this->status;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'from'     => $this->from(),
            'to'       => $this->to(),
            'status'   => $this->status(),
            'priority' => $this->priority(),
            'comment'  => $this->comment()
        ];
    }

    /**
     * Replaces placeholders in $path string
     *
     * @param string $path
     * @param array<int, string> $placeholders
     * @return string
     */
    public static function toPath(string $path, array $placeholders = []): string
    {
        // Replace alias for home
        if ($path === '/') {
            return 'home';
        }

        foreach ($placeholders as $i => $placeholder) {
            $path = str_replace('$' . ($i + 1), $placeholder, $path);
        }

        return $path;
    }

    /**
     * Return route definition for Router
     *
     * @return array|false
     */
    public function toRoute()
    {
        if ($this->isActive() === false) {
            return false;
        }

        $redirect = $this;

        return [
            'pattern' => $this->from(),
            'action'  => function (array ...$placeholders) use ($redirect) {

                /** @var array<int, string> $placeholders */
                $to = $redirect->to() ?? '/';
                $to = Redirect::toPath($to, $placeholders);

                /** @var int $code */
                $code = $redirect->status();

                // Add log entry
                Plugin::instance()->log()->add([
                    'path'     => Url::path(),
                    'redirect' => $redirect->from()
                ]);

                // Redirects
                // @codeCoverageIgnoreStart
                if ($code >= 300 && $code < 400) {
                    go($to, $code);
                }
                // @codeCoverageIgnoreEnd

                // Set the right response code
                kirby()->response()->code($code);

                // Return page for other codes
                if ($page = page($to)) {
                    return $page;
                }

                // Deliver HTTP status code and die
                // @codeCoverageIgnoreStart
                Header::status($code);
                die();
                // @codeCoverageIgnoreEnd
            }
        ];
    }
}
