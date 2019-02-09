<?php

namespace distantnative\Retour;

class Logs extends Log
{

    public static $file = '/logs/retour/404.log';

    public function add(array $temporaries): void
    {
        $data = $this->data();

        foreach ($temporaries as $tmp) {
            $id = $tmp['path'] . '$' . $tmp['referrer'];

            if (isset($data[$id]) === false) {
                $data[$id] = [
                    'path'      => $tmp['path'],
                    'referrer'  => $tmp['referrer'],
                    'fails'     => 0,
                    'redirects' => 0,
                    'last'      => null
                ];
            }

            $data[$id][$tmp['isFail'] ? 'fails' : 'redirects']++;
            $data[$id]['last'] = $tmp['date'];
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
