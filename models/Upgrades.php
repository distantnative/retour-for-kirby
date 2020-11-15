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
     * Loads current release info from getkirby.com
     *
     * @param bool $reload
     *
     * @return string|null
     */
    public static function latest(bool $reload = false): ?string
    {
        $kirby  = kirby();
        $option = $kirby->option('update.kirby') ?? $kirby->option('update');

        if ($reload === true || $option !== false) {
            $cache  = $kirby->cache('retour');
            $cached = $cache->get('release');

            if ($cached === null || $reload === true) {
                $url = 'https://getkirby.com/plugins/distantnative/retour.json';
                $response = Remote::get($url)->json();
                $cached = $response['version'];
                $cache->set('release', $cached, 60 * 24);
            }

            return $cached;
        }

        return null;
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
