<?php

namespace distantnative\Retour;

use Kirby\Cms\Response;
use Kirby\Http\Header;
use Kirby\Http\Url;

class Redirects extends Log
{
    public static $file;

    public static function flush(): bool
    {
        return static::update(function ($redirect) {
            $redirect['hits'] = null;
            $redirect['last'] = null;
            return $redirect;
        });
    }

    public static function hit(array $temporaries): bool
    {
        // get existing redirects data
        $data = static::read();

        // loop through all temporaries and update
        foreach ($temporaries as $item) {
            $froms = array_column($data, 'from');
            $key   = array_search($item['pattern'], $froms);
            $data[$key]['hits'] = ($data[$key]['hits'] ?? 0) + 1;
            $data[$key]['last'] = date('Y-m-d H:i');
        }

        return static::write($data);
    }

    public static function routes(\Kirby\Cms\App $kirby): array
    {
        // no routes for disabled redirects
        $data = array_filter(static::read(), function ($redirect) {
            return $redirect['status'] !== 'disabled';
        });

        return array_map(function ($redirect) use ($kirby) {
            return [
                'name'    => $redirect['from'],
                'pattern' => $redirect['from'],
                'action'  => function (...$parameters) use ($redirect, $kirby) {
                    $code = (int)$redirect['status'];
                    $to   = $redirect['to'];

                    // Store temporary log file to process later
                    Retour::store(
                        Url::path(),
                        'redirected',
                        $redirect['from']
                    );

                    // Set the right response code
                    $kirby->response()->code($code);

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
}
