<?php

namespace distantnative\Retour;

use Kirby\Cms\App;
use Kirby\Cms\Response;
use Kirby\Data\Data;
use Kirby\Http\Header;
use Kirby\Http\Url;

class Redirects
{

    public static $file;

    /**
     * Get all redirects from file
     *
     * @return array
     */
    public static function read(): array
    {
        try {
            return Data::read(self::$file, 'yaml');
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Read all redirects and combine them with records data
     *
     * @return array
     */
    public static function list(): array
    {
        $log = new Log;
        return array_map(function ($redirect) use ($log) {
            return $log->forRedirect($redirect);
        }, self::read());
    }

    /**
     * Get routes config for all redirects
     *
     * @param App $kirby
     * @return array
     */
    public static function routes(Log $log): array
    {
        // no routes for disabled redirects
        $data = array_filter(self::read(), function ($redirect) {
            return $redirect['status'] !== 'disabled';
        });

        return array_map(function ($redirect) use ($log) {
            return [
                'pattern' => $redirect['from'],
                'action'  => function (...$parameters) use ($redirect, $log) {
                    $code = (int)$redirect['status'];
                    $to   = $redirect['to'];

                    // Create log record
                    $log->add([
                        'path' => Url::path(),
                        'redirect' => $redirect['from']
                    ]);
                    $log->close();

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
    }

    /**
     * Update redirects in file
     *
     * @param array $data
     * @return bool
     */
    public static function write(array $data = []): bool
    {
        return Data::write(self::$file, $data, 'yaml');
    }
}
