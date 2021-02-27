<?php

namespace distantnative\Retour;

use Kirby\Http\Remote;

class Migrator
{

    protected $current;
    protected $migrations;

    public function __construct(string $current, array $migrations)
    {
        $this->current    = $current;
        $this->migrations = $migrations;
    }

    public function hasUpgrades(): bool
    {
        return version_compare($this->current, $this->latest(), '<') === true;
    }

    public function latest() {
        return array_key_last($this->migrations);
    }

    public function run()
    {
        if ($this->hasUpgrades() === false) {
            return false;
        }

        foreach ($this->migrations as $version => $callback) {
            if (version_compare($version, $this->current, '<=') === true) {
                break;
            }

            $callback();
        }

        return $this->latest();
    }
}
