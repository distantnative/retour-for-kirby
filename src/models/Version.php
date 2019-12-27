<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

class Version
{

    public static $migrations = [];

    /**
     * Returns path to version file
     *
     * @return string
     */
    static protected function file(): string
    {
        return dirname(Retour::root('redirects')) . '/.retour';
    }

    /**
     * Returns version number of current plugin code
     *
     * @return string
     */
    static protected function ofCode(): string
    {
        $plugin  = Data::read(Retour::root() . '/composer.json');
        return $plugin['version'];
    }

    /**
     * Returns version number of stored plugin data
     *
     * @return string
     */
    static protected function ofData(): string
    {
        $file = static::file();

        if (F::exists($file) === true) {
            return F::read($file);
        }

        return '2.2.1';
    }

    /**
     * Check for migration updates and run them
     *
     * @return void
     */
    public static function update(): void
    {
        $code = static::ofCode();
        $data = static::ofData();

        // If data is not older than code, skip
        if (version_compare($data, $code, "<") === false) {
            return;
        }

        // Run update code for each previous version
        $migrations = usort(static::$migrations, 'version_compare');
        foreach($migrations as $migration => $callback) {
            if (version_compare($data, $migration, "<=")) {
                call_user_func($callback);
            }
        }

        // Write current version to data file
        F::write(static::file(), $code);
    }
}

Version::$migrations['3.3.0'] = function() {

};
