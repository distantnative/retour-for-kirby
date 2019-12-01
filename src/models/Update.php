<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Database\Db;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;

class Update
{

    protected static $migrations = [];

    public static function check(): void
    {
        $flag    = Retour::root('updated');
        $plugin  = Data::read(Retour::root() . '/composer.json');
        $version = $plugin['version'];

        if (F::exists($flag) === true) {
            $previous = F::read($flag);
            if (version_compare($previous, $version, "<") === false) {
                return;
            }
        }

        self::forPHP($version);

        if (F::exists(Retour::root('logs')) === true) {
            $log = new Log;
            self::forSQL($version);
            $log->close();
        }

        F::write($flag, $version);
    }

    protected static function forPHP(string $version)
    {
        foreach(self::$migrations as $migration => $callback) {
            if (version_compare($version, $migration, "<=")) {
                call_user_func($callback);
            }
        }
    }

    protected static function forSQL(string $version)
    {
        $dir = dirname(__DIR__) . '/config/migrations';

        foreach(Dir::read($dir) as $migration) {
            if (
                Str::endsWith($migration, '.sql') &&
                version_compare($version, Str::before($migration, '.sql'), "<=")
            ) {
                $queries = F::read($dir . '/' . $migration);
                $queries = explode(';', $queries);

                foreach ($queries as $query) {
                    Db::execute($query);
                }
            }
        }
    }
}
