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
     * @param string $from
     * @param string $to
     *
     * @return array
     */
    public function get(string $from, string $to): array
    {
        $redirects = $this->load();

        // If logging is disabled, return without data for redirects
        if (option('distantnative.retour.logs') !== true) {
           return $redirects;
        }

        return array_map(function ($redirect) use ($from, $to) {
            return Retour::instance()->logs()->redirect($redirect, $from, $to);
        }, $redirects);
    }

    /**
     * Get routes config for all redirects
     *
     * @return array
     */
    public function toRoutes(): array
    {
        $data = $this->load();

        // Filter: no routes for disabled redirects
        $data = array_filter($data, function ($redirect) {
            return $redirect['status'] !== 'disabled';
        });

        // create route array for each redirect
        $logs = Retour::instance()->logs();
        $data = array_map(function ($redirect) use ($logs) {
            return [
                'pattern' => $redirect['from'],
                'action'  => function (...$parameters) use ($redirect, $logs) {
                    $code = (int) $redirect['status'];
                    $to   = $redirect['to'];

                    // Create log record
                    if (option('distantnative.retour.logs') === true) {
                        $logs->create([
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
        }, $data);

        return $data;
    }

    /**
     * Update redirects in file
     *
     * @param array $data
     */
    public static function update(array $data = [])
    {
        return Data::write($this->config, $data, 'yaml');
    }
}
