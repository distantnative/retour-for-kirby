<?php

namespace distantnative\Retour;

use Kirby\Cms\Response;
use Kirby\Data\Data;
use Kirby\Http\Header;
use Kirby\Http\Url;

class Redirects
{

    /**
     * Get all redirects from file
     *
     * @return array
     */
    public static function read(): array
    {
        try {
            $file = Retour::root('redirects');
            return Data::read($file, 'yaml');
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
    public static function list(string $from, string $to): array
    {
        $redirects = static::read();

        // If logging is enabled, add log data for redirects
        if (option('distantnative.retour.logs') === true) {
            $log = new Log;
            $redirects = array_map(function ($redirect) use ($log, $from, $to) {
                return $log->forRedirect($redirect, $from, $to);
            }, $redirects);
        }

        return $redirects;
    }

    /**
     * Get routes config for all redirects
     *
     * @return array
     */
    public static function routes(): array
    {
        // Get all redirects
        $data = static::read();

        // Filter: no routes for disabled redirects
        $data = array_filter($data, function ($redirect) {
            return $redirect['status'] !== 'disabled';
        });

        // create route array for each redirect
        $data = array_map(function ($redirect) {
            return [
                'pattern' => $redirect['from'],
                'action'  => function (...$parameters) use ($redirect) {
                    $code = (int) $redirect['status'];
                    $to   = $redirect['to'];

                    // Create log record
                    if (option('distantnative.retour.logs') === true) {
                        (new Log)->add([
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
     *
     * @return bool
     */
    public static function write(array $data = []): bool
    {
        $file = Retour::root('redirects');
        return Data::write($file, $data, 'yaml');
    }
}
