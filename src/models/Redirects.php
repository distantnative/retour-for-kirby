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
            $file = Retour::root('redirects');
            return Data::read($file, 'yaml');
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Read all redirects and combine them with records data
     *
     * @return array
     */
    public static function list(string $from, string $to): array
    {
        $redirects = self::read();

        if (option('distantnative.retour.logs') === true) {
            $log = new Log;
            $redirects = array_map(function ($r) use ($log, $from, $to) {
                return $log->forRedirect($r, $from, $to);
            }, $redirects);
        }

        return $redirects;
    }

    /**
     * Get routes config for all redirects
     *
     * @param Log|bool $log
     * @return array
     */
    public static function routes($log): array
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
                    if ($log !== false) {
                        $log->add([
                            'path' => Url::path(),
                            'redirect' => $redirect['from']
                        ]);
                        $log->close();
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
    }

    /**
     * Update redirects in file
     *
     * @param array $data
     * @return bool
     */
    public static function write(array $data = []): bool
    {
        $file = Retour::root('redirects');
        return Data::write($file, $data, 'yaml');
    }
}
