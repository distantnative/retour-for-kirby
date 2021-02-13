<?php

namespace distantnative\Retour;

use Kirby\Http\Remote;

class Upgrades
{

    /**
     * Whether config schema version if lower than
     * current code version
     *
     * @return bool
     */
    protected function hasUpgrades(): bool
    {
        $schema  = $this->schema();
        $version = $this->version();

        return version_compare($schema, $version, '<') === true;
    }

    /**
     * Check for upgrades and run the migrations
     *
     * @return void
     */
    public function run(): void
    {
        $retour = retour();

        if ($this->hasUpgrades() === true) {
            $migrations = require dirname(__DIR__) . '/config/migrations.php';
            foreach ($migrations as $schema => $migration) {
                if (version_compare($schema, $this->schema(), '<=') === true) {
                    break;
                }

                call_user_func($migration, $retour);
            }

            $retour->update($schema, 'schema');
        }
    }

    /**
     * Returns the schema version from config file
     *
     * @return string
     */
    protected function schema(): string
    {
        return retour()->config['schema'] ?? '2.3.1';
    }

    /**
     * Return the current code version
     *
     * @return string
     */
    protected function version(): string
    {
        return retour()->plugin()->version();
    }
}
