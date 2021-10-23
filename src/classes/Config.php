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
        if (is_callable($file) === true) {
            $file = (string)$file();
        }

        static::$file = $file;

        try {
            static::$data = Data::read(static::$file);
        } catch (\Throwable $th) {
            static::$data = [];
        }

        return static::$data;
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
        /** @var string|null static::$file */
        if (static::$file === null) {
            throw new LogicException('Config::write is called before data waas loaded for the first time');
        }

        return Data::write(static::$file, static::$data);
    }


}
