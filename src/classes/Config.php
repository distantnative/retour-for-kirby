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
     *
     * @var string|null
     */
    public static $file = null;

    /**
     * Loads config from file and sets file path
     *
     * @param string|callable $file absolute path to config file
     * @return array
     */
    public static function load($file): array
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
     *
     * @param string|array $key
     * @param mixed $value
     * @return array
     */
    public static function set($key, $value = null): array
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
     * @return string
     */
    protected static function file($file): string
    {
        if (is_callable($file) === true) {
            return $file();
        }

        return $file;
    }
}
