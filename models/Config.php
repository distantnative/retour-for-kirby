<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Exception\LogicException;
use Kirby\Toolkit\Silo;

class Config extends Silo
{
    /**
     * @var string
     */
    protected static $file;

    /**
     * Loads config from file and sets static file path
     *
     * @param string $file absolute path to config yaml file
     * @return array
     */
    public static function load(string $file): array
    {
        static::$file = $file;

        if (is_callable(static::$file) === true) {
            static::$file = call_user_func(static::$file);
        }

        try {
            static::$data = Data::read(static::$file, 'yaml');
        } catch (\Throwable $th) {
            static::$data = [];
        }

        return static::$data;
    }

    /**
     * Sets the config but also writes it to config file
     *
     * @param string|array $key
     * @param mixed $value
     * @return array
     */
    public static function set($key, $value = null): array
    {
        $data = parent::set($key, $value);
        static::write();
        return $data;
    }

    /**
     * Writes current config to yaml file
     *
     * @return bool
     */
    public static function write(): bool
    {
        if (static::$file === null) {
            throw new LogicException('Config::write is called before data waas loaded for the first time');
        }

        return Data::write(static::$file, static::$data, 'yaml');
    }
}
