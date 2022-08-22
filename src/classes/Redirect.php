<?php

namespace distantnative\Retour;

use Kirby\Exception\InvalidArgumentException;
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
     * Class constructor, validates data
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if (empty($data['from'] ?? null) === true) {
            throw new InvalidArgumentException('Route requires path');
        }

        parent::__construct($data);
    }

    /**
     * Use redirect pattern as id for object
     *
     * @return string
     */
    public function id(): string
    {
        /**
         * Fix for issue #300 (See https://github.com/distantnative/retour-for-kirby/issues/300):
         *
         * Depending on the settings, the webserver might not always handle
         * escaped forward-slashes in the way, which this plugin expects it to.
         *
         * This specifically results in a 404 when trying to edit a redirect-entry,
         * unless the ```AllowEncodedSlashes NoDecode``` is set for the Apache
         * Server. The problem occurs in relation to nginx.
         *
         * Many hosting solutions does now allow customers to change such
         * settings for the web-server, and so another solution is required.
         *
         * So, in order to remedy this problem, we replace forward-slash with the
         * non-visible ascii-characer "GROUP-SEPARATOR" (Oct: 035, Dec: 29,
         * Hex: 1D). By using a non-visible chracter we ensure that the id
         * generation from redirect pattern is always unique.
         *
         * Note that this fix include changes to two parts of the plug-in
         * code-base. In this file, and in src/panel/components/Tabs/RedirectsTab.vue
         */
        return str_replace('/', "\x1D", $this->from());
    }

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
            'action'  => function (...$placeholders) use ($redirect) {

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
                $page = page($to);
                if ($page !== null) {
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
