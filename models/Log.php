<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

class Log
{
    public static $file;

    protected static function defaults(): array
    {
        return [];
    }

    protected static function file($suffix = 'retour'): string
    {
        return str_replace('{x}', $suffix, static::$file);
    }

    public static function read($suffix = 'retour'): array
    {
        if (F::exists(static::file($suffix)) === false) {
            return static::defaults();
        }

        return Data::read(static::file($suffix), 'yaml');
    }

    public static function update(callable $callback)
    {
        $data = static::read();
        $data = array_map($callback, $data);
        return static::write($data);
    }

    public static function write(array $data = [], $suffix = 'retour'): bool
    {
        return Data::write(static::file($suffix), $data, 'yaml');
    }
}
