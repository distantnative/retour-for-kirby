<?php

namespace distantnative\Retour;

use Kirby\Http\Remote;

class Upgrades
{

    /**
     * @param \distantnative\Retour\Retour $retour
     */
    protected $retour;

    /**
     * @var string
     */
    protected $version;

    /**
     * @param \distantnative\Retour\Retour $retour
     */
    public function __construct(Retour $retour)
    {
        $this->retour = $retour;
    }

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

        return version_compare($schema, $version) < 0;
    }

    /**
     * Check for upgrades and run the migrations
     *
     * @return void
     */
    public function run(): void
    {
        if ($this->hasUpgrades() === true) {
            $migrations = require dirname(__DIR__) . '/config/migrations.php';
            foreach ($migrations as $schema => $migration) {
                if (version_compare($schema, $this->version()) <= 0) {
                    call_user_func($migration, $this->retour);
                    $this->retour->update($schema, 'schema');
                }
            }
        }
    }

    /**
     * Returns the schema version from config file
     *
     * @return string
     */
    protected function schema(): string
    {
        return $this->retour->config['schema'] ?? '2.3.1';
    }

    /**
     * Return the current code version
     *
     * @return string
     */
    protected function version(): string
    {
        return $this->version = $this->version ?? $this->retour->plugin()->version();
    }
}
