<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

class Store {

    protected $data;
    protected $file;

    public function data(string $suffix = null)
    {
        if ($this->data) {
            if ($suffix) {
                if (isset($this->data[$suffix])) {
                    return $this->data[$suffix];
                }
            } else {
                return $this->data;
            }
        }

        if ($suffix) {
            return $this->data[$suffix] = $this->read($suffix);
        }

        return $this->data = $this->read($suffix);
    }

    protected static function defaults(): array
    {
        return [];
    }

    public function read(string $suffix = null)
    {
        $file = str_replace('{x}', $suffix, $this->file);

        if (F::exists($file) === false) {
            return static::defaults();
        }

        return Data::read($file, 'yaml');
    }

    public function write(array $data = [], string $suffix = null)
    {
        Data::write(str_replace('{x}', $suffix, $this->file), $data, 'yaml');

        if ($suffix) {
            return $this->data[$suffix] = $data;
        }

        return $this->data = $data;
    }

}
