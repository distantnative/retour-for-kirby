<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

class Log
{
    public static $file;

    protected $data;

    public function data(string $suffix = 'retour')
    {
        return $this->data[$suffix] = $this->data[$suffix] ?? $this->read($suffix);
    }

    protected static function defaults(): array
    {
        return [];
    }

    public function file($suffix = 'retour'): string
    {
        $file = str_replace('{x}', $suffix, static::$file);
        return kirby()->root('site') . $file;
    }

    public function read($suffix = null)
    {
        if (F::exists($this->file($suffix)) === false) {
            return static::defaults();
        }

        return Data::read($this->file($suffix), 'yaml');
    }

    public function write(array $data = [], $suffix = null)
    {
        Data::write($this->file($suffix), $data, 'yaml');

        return $this->data[$suffix] = $data;
    }
}
