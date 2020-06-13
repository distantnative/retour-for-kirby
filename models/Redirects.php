<?php

namespace distantnative\Retour;

use Kirby\Cms\Response;
use Kirby\Data\Data;
use Kirby\Http\Header;
use Kirby\Http\Url;

class Redirects
{

    protected $config;

    public function __construct()
    {
        $this->config = option('distantnative.retour.config');

        if (is_callable($this->config) === true) {
            $this->config = call_user_func($this->config);
        }
    }

    /**
     * Get all redirects from file
     *
     * @return array
     */
    public function load()
    {
        try {
            return Data::read($this->config, 'yaml');
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Read all redirects and combine them with records data
     *
     * @param string $begin
     * @param string $end
     *
     * @return array
     */
    public function get(string $begin, string $end): array
    {
        $redirects = $this->load();

        // If logging is disabled, return without data for redirects
        if (option('distantnative.retour.logs') !== true) {
           return $redirects;
        }

        return array_map(function ($redirect) use ($begin, $end) {
            return Retour::instance()->logs()->redirect(
                $redirect,
                $begin,
                $end
            );
        }, $redirects);
    }

    protected function toRoute(array $redirect): array
    {
        return [
            'pattern' => $redirect['from'],
            'action'  => function (...$parameters) use ($redirect) {
                $code = (int)$redirect['status'];
                $to   = $redirect['to'];

                // Create log record
                if (option('distantnative.retour.logs') === true) {
                    Retour::instance()->logs()->create([
                        'path' => Url::path(),
                        'redirect' => $redirect['from']
                    ]);
                }

                // Set the right response code
                kirby()->response()->code($code);

                // Map placeholders/parameters
                foreach ($parameters as $i => $parameter) {
                    $to = str_replace('$' . ($i + 1), $parameter, $to);
                }

                // Replace alias for home
                if ($to === '/') {
                    $to = 'home';
                }

                // Redirects
                if ($code >= 300 && $code < 400) {
                    return Response::redirect($to ?? '/', $code);
                }

                // Return page for other codes
                if ($page = page($to)) {
                    return page($page);
                }

                // Deliver HTTP status code and die
                Header::status($code);
                die();
            }
        ];
    }

    /**
     * Get routes config for all redirects
     *
     * @return array
     */
    public function toRoutes(bool $priority = false): array
    {
        $data = $this->load();

        // Filter: no routes for disabled redirects
        //         and match the priority parameter
        $data = array_filter($data, function ($redirect) use ($priority) {
            return empty($redirect['status']) === false &&
                   (bool)($redirect['priority'] ?? false) === $priority;
        });

        // create route array for each redirect
        $data = array_map([$this, 'toRoute'], $data);

        return $data;
    }

    /**
     * Update redirects in file
     *
     * @param array $data
     */
    public function update(array $data = [])
    {
        return Data::write($this->config, $data, 'yaml');
    }
}
