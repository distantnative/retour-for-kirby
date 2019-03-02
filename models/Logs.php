<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

class Logs extends Log
{
    public static $dir  = __DIR__ . '/../../../logs/retour';
    public static $file = __DIR__ . '/../../../logs/retour/404.log';

    public static function add(array $temporaries): bool
    {
        $data = static::read();

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

        return static::write($data);
    }

    public static function flush(): bool
    {
        return Dir::remove(static::$dir);
    }

     /**
     * @codeCoverageIgnore
     */
    public static function process(): bool
    {
        $tmp = static::temporaries();

        if (empty($tmp) === false) {
            static::add($tmp);
            Stats::count($tmp);
            Redirects::hit(array_filter($tmp, function ($x) {
                return $x['status'] === 'redirected';
            }));
        }

        return true;
    }

    public static function store(string $path, string $status, string $pattern = null): bool
    {
        $file = static::$dir . '/.' . md5($path) . '.' . time() . '.tmp';

        return Data::write($file, [
            'path'     => $path,
            'referrer' => $_SERVER['HTTP_REFERER'] ?? null,
            'status'   => $status,
            'pattern'  => $pattern,
            'date'     => date('Y-m-d H:i')
        ], 'yaml');
    }

    public static function temporaries(): array
    {
        $tmp   = [];
        $files = static::$dir . '/.*.tmp';

        foreach (glob($files) as $file) {
            $tmp[] = Data::read($file, 'yaml');
            F::remove($file);
        }

        return $tmp;
    }
}
