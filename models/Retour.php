<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

class Retour
{
    protected $logs;
    protected $redirects;
    protected $stats;
    protected $system;

    public static $dir;


    public static function flush(): bool
    {
        Redirects::flush();
        return Dir::remove(static::$dir);
    }

    /**
     * @codeCoverageIgnore
     */
    public static function process(): bool
    {
        $tmp = static::temporaries();

        if (empty($tmp) === false) {
            Logs::add($tmp);
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
