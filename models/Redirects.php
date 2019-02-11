<?php

namespace distantnative\Retour;

use Kirby\Cms\Response;
use Kirby\Http\Header;
use Kirby\Http\Url;
use Kirby\Toolkit\Collection;

class Redirects extends Log
{
    public function flush(): void
    {
        $data = $this->data();

        foreach ($data as $key => $redirect) {
            $data[$key]['hits'] = null;
            $data[$key]['last'] = null;
        }

        $this->write($data);
    }

    public function hit(array $temporaries): void
    {
        // get existing redirects data
        $data = $this->data();

        // loop through all temporaries and update
        foreach ($temporaries as $item) {
            $froms = array_column($data, 'from');
            $key   = array_search($item['pattern'], $froms);
            $data[$key]['hits'] = ($data[$key]['hits'] ?? 0) + 1;
            $data[$key]['last'] = date('Y-m-d H:i');
        }

        $this->write($data);
    }

    public function read($suffix = null)
    {
        return site()->retour()->yaml();
    }

    public function routes(): array
    {
        // no routes for disabled redirects
        $data = array_filter($this->data(), function ($redirect) {
            return $redirect['status'] !== 'disabled';
        });

        return array_map(function ($redirect) {
            return [
                'name'    => $redirect['from'],
                'pattern' => $redirect['from'],
                'action'  => function (...$parameters) use ($redirect) {
                    $code = (int)$redirect['status'];
                    $to   = $redirect['to'];

                    // Store temporary log file to process later
                    Retour::store(
                        Url::path(),
                        'redirected',
                        $redirect['from']
                    );

                    // Set the right response code
                    kirby()->response()->code($code);

                    // Map placeholders/parameters
                    foreach ($parameters as $i => $parameter) {
                        $to = str_replace('$' . ($i + 1), $parameter, $to);
                    }

                    // Redirects
                    if ($code >= 300 && $code < 400) {
                        return Response::redirect($to ?? '/', $code);
                    }

                    // Return page for other codes
                    if ($to) {
                        return page($to ?? 'error');
                    }

                    // Deliver HTTP status code and die
                    Header::status($code);
                    die();
                }
            ];
        }, $data);
    }

    public function write(array $data = [], $suffix = null): void
    {
        site()->update(['retour' => $data]);
    }
}
