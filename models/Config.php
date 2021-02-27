<?php

namespace distantnative\Retour;

use Kirby\Data\Data;

class Config
{

    protected $file;
    public $data;

    public function __construct(string $file)
    {
        $this->file = $file;

        if (is_callable($this->file) === true) {
            $this->file = call_user_func($this->file);
        }

        $this->read();
    }

    public function data(?string $key = null, $fallback = null)
    {
        if ($key !== null) {
            return $this->data[$key] ?? $fallback ?? null;
        }

        return $this->data;
    }

    public function overwrite(array $data): bool
    {
        $this->data = $data;
        return $this->write();
    }

    protected function read(): void
    {
        try {
            $this->data = Data::read($this->file, 'yaml');
        } catch (\Throwable $th) {
            $this->overwrite([]);
        }
    }

    public function set(string $key, $value): bool
    {
        $this->data[$key] = $value;
        return $this->write();
    }

    protected function write(): bool
    {
        return Data::write($this->file, $this->data, 'yaml');
    }
}
