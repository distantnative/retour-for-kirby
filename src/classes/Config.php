<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Exception\LogicException;
use Kirby\Toolkit\Silo;

/**
 * Config
 * Handles reading from and writing to the config file.
 * Stores the data as singleton silo.
 *
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */
class Config extends Silo
{
    /**
     * Absolute path to the config file
     */
    public static string|null $file = null;

    /**
     * Loads config from file and sets file path
     *
     * @param string|callable $file absolute path to config file
     */
    public static function load(string|callable $file): array
    {
        static::$file = static::file($file);

        try {
            return static::$data = Data::read(static::$file);
        } catch (\Throwable $th) {
            return static::$data = [];
        }
    }

    /**
     * Sets the config value
     * but also writes it directly to file
     */
    public static function set(string|array $key, mixed $value = null): array
    {
        if (static::$file === null) {
            throw new LogicException('Config::write is called before data waas loaded for the first time');
        }

        // update data
        $data = parent::set($key, $value);

        // write data to config file
        if (Data::write(static::$file, $data) === true) {
            return $data;
        }

        throw new LogicException('Config::write failed');
    }

    /**
     * Resolves file definition to path string
     *
     * @param string|callable $file absolute path to config file
     */
    protected static function file(string|callable $file): string
    {
        if (is_callable($file) === true) {
            return $file();
        }

        return $file;
    }
}
