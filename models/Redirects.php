<?php

namespace distantnative\Retour;

use Kirby\Cms\Response;
use Kirby\Http\Header;
use Kirby\Toolkit\Collection;

class Redirects extends Store
{
    protected $retour;

    public function hit(array $tmp): void
    {
        $data = $this->data();

        foreach ($tmp as $item) {
            $key  = array_search($item['pattern'], array_column($data, 'from'));
            $data[$key]['hits'] = ($data[$key]['hits'] ?? 0) + 1;
            $data[$key]['last'] = date('Y-m-d H:i');
        }

        $this->write($data);
    }

    public function read(string $suffix = null)
    {
        return $this->data = site()->retour()->yaml();
    }

    public function routes(): array
    {
        $data = array_filter($this->data(), function ($redirect) {
            return $redirect['status'] !== 'disabled';
        });

        return array_map(function ($redirect) {
            return [
                'name'    => $redirect['from'],
                'pattern' => $redirect['from'],
                'action'  => function (...$parameters) use ($redirect) {
                    $to   = $redirect['to'];
                    $code = (int)$redirect['status'];

                    foreach ($parameters as $i => $parameter) {
                        $to = str_replace('$' . ($i + 1), $parameter, $to);
                    }

                    kirby()->response()->code($code);

                    if ($code >= 300 && $code < 400) {
                        return Response::redirect($to ?? '/', $code);
                    }

                    if ($to) {
                        return page($to ?? 'error');
                    }

                    Header::status($code);
                    die();
                }
            ];
        }, $data);
    }

    public function write(array $data = [], string $suffix = null): void
    {
        site()->update(['retour' => $data]);
    }
}
