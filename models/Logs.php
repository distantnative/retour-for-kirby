<?php

namespace distantnative\Retour;

class Logs extends Log
{
    public static $file;

    public function add(array $temporaries): void
    {
        $data = $this->data();

        foreach ($temporaries as $tmp) {
            $id = $tmp['path'] . '$' . $tmp['referrer'];

            if (isset($data[$id]) === false) {
                $data[$id] = [
                    'path'       => $tmp['path'],
                    'referrer'   => $tmp['referrer'],
                    'failed'     => 0,
                    'redirected' => 0,
                    'last'       => null
                ];
            }

            $data[$id][$tmp['status']]++;
            $data[$id]['last'] = $tmp['date'];
        }

        $this->write($data);
    }
}
