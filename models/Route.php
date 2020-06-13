<?php

namespace distantnative\Retour;

use Kirby\Cms\Response;
use Kirby\Http\Header;
use Kirby\Http\Url;


class Route
{

    /**
     * @var array
     */
    protected $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    protected function __call($name, $arguments)
    {
        return $this->options[$name] ?? null;
    }

    /**
     * Whether the route takes priority over actual pages
     *
     * @return bool
     */
    public function hasPriority(): bool
    {
        return $this->options['priority'] ?? false === true;
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
        if (isset($this->options['status']) === false) {
            return null;
        }

        if (empty($this->options['status']) === true) {
            return null;
        }

        if ($this->options['status'] === 'disabled') {
            return null;
        }

        return (int)$this->options['status'];
    }

    public function toArray()
    {
        return [
            'status'   => $this->status(),
            'from'     => $this->from(),
            'to'       => $this->to(),
            'priority' => $this->priority() === true,
            'comment'  => $this->comment()
        ];
    }

    /**
     * Return route definition for Router
     *
     * @return array
     */
    protected function toRule(): array
    {
        $route = $this;

        return [
            'pattern' => $route->from(),
            'action'  => function (...$parameters) use ($route) {

                // Create log record
                if (option('distantnative.retour.logs') === true) {
                    Retour::instance()->log()->create([
                        'path'     => Url::path(),
                        'redirect' => $route->from()
                    ]);
                }

                // Set the right response code
                $status = $route->status();
                kirby()->response()->code($status);

                $to = $route->to();


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
