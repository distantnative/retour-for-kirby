<?php

namespace distantnative\Retour;

class Log extends Store
{

    public function __construct()
    {
        $this->file = kirby()->root('site') . '/logs/retour/404.log';
    }

    public function add(array $tmp): void
    {
        $data = $this->data();

        foreach ($tmp as $item) {
            $id   = $item['path'] . '$' . $item['referrer'];

            if (isset($data[$id]) === false) {
                $data[$id] = [
                    'path'      => $item['path'],
                    'referrer'  => $item['referrer'],
                    'fails'     => 0,
                    'redirects' => 0,
                    'last'      => null
                ];
            }

            $data[$id][$item['isFail'] ? 'fails' : 'redirects']++;
            $data[$id]['last'] = $item['date'];
        }

        $this->write($data);
    }

    public function fails(string $sort = 'fails'): array
    {
        // remove redirect-only logs
        $data = array_filter($this->data(), function ($log) {
            return ($log['fails'] ?? 0) !== 0;
        });

        // sort accordingly
        usort($data, function ($log1, $log2) use ($sort) {
            return $log2[$sort] <=> $log1[$sort];
        });

        return $data;
    }

}
