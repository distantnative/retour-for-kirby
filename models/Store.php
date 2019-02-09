<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

class Store
{
    public static $file;
    protected $data;

    public function data(string $suffix = 'retour')
    {
        if ($this->data && isset($this->data[$suffix]) === true) {
            return $this->data[$suffix];
        }

        return $this->data[$suffix] = $this->read($suffix);
    }

    protected static function defaults(): array
    {
        return [];
    }

    public function file(): string
    {
        return static::$file;
    }

    public function read(string $suffix = 'retour')
    {
        $file = kirby()->root('site') . str_replace('{x}', $suffix, static::$file);

        if (F::exists($file) === false) {
            return static::defaults();
        }

        return Data::read($file, 'yaml');
    }

    public function write(array $data = [], string $suffix = 'retour')
    {
        Data::write(kirby()->root('site') . str_replace(
            '{x}',
            $suffix,
            static::$file
        ), $data, 'yaml');

        return $this->data[$suffix] = $data;
    }
}
