<?php

namespace distantnative\Retour;

use Kirby\Http\Header;
use Kirby\Http\Response;
use Kirby\Http\Url;
use Kirby\Toolkit\Obj;
use Kirby\Toolkit\Str;

class Redirect extends Obj
{
    /**
     * Whether the route is enabled with int status code
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status() !== null;
    }

    /**
     * Whether the route takes priority over actual pages
     *
     * @return bool
     */
    public function priority(): bool
    {
        return Str::toType($this->priority ?? null, 'bool') === true;
    }

    /**
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

    public static function resolve(string $from, string $to, int $status, array $placeholders)
    {
        // Add log entry
        retour()->log()->add([
            'path'     => Url::path(),
            'redirect' => $from
        ]);

        // Redirects
        if ($status >= 300 && $status < 400) {
            return Response::redirect($to ?? '/', $status);
        }

        // Set the right response code
        kirby()->response()->code($status);

        // Return page for other codes
        if ($page = page($to)) {
            return $page;
        }

        // Deliver HTTP status code and die
        Header::status($status);
        die();
    }

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
     * @return array|bool
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
                return static::resolve(
                    $redirect->from(),
                    static::toPath($redirect->to(), $placeholders),
                    $redirect->status(),
                    $placeholders
                );
            }
        ];
    }
}
