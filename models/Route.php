<?php

namespace distantnative\Retour;

use Kirby\Cms\Response;
use Kirby\Http\Header;
use Kirby\Http\Url;
use Kirby\Toolkit\Obj;

class Route extends Obj
{

    /**
     * Whether the route takes priority over actual pages
     *
     * @return bool
     */
    public function hasPriority(): bool
    {
        return ($this->priority() ?? false) === true;
    }

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
     * @return int|null
     */
    public function status(): ?int
    {
        if ($this->status === null || $this->status === 'disabled') {
            return null;
        }

        return (int)$this->status;
    }

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
     * Return route definition for Router
     *
     * @return array
     */
    public function toRule(): array
    {

        $from   = $this->from();
        $to     = $this->to();
        $status = $this->status();

        if ($status === null) {
            return [];
        }

        return [
            'pattern' => $from,
            'action'  => function (...$parameters) use ($from, $to, $status) {

                // Create log record
                if (option('distantnative.retour.logs', true) === true) {
                    retour()->log()->create([
                        'path'     => Url::path(),
                        'redirect' => $from
                    ]);
                }

                // Set the right response code
                kirby()->response()->code($status);

                // Map placeholders/parameters
                foreach ($parameters as $i => $parameter) {
                    $to = str_replace('$' . ($i + 1), $parameter, $to);
                }

                // Replace alias for home
                if ($to === '/') {
                    $to = 'home';
                }

                // Redirects
                if ($status >= 300 && $status < 400) {
                    return Response::redirect($to ?? '/', $status);
                }

                // Return page for other codes
                if ($page = page($to)) {
                    return $page;
                }

                // Deliver HTTP status code and die
                Header::status($status);
                die();
            }
        ];
    }
}
