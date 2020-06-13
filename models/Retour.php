<?php

namespace distantnative\Retour;

use Kirby\Http\Header;
use Kirby\Http\Remote;

class Retour
{
    /**
     * @var \distantnative\Retour\Retour
     */
    protected static $instance = null;


    /**
     * @var \distantnative\Retour\Log
     */
    protected $log = null;

    /**
     * @var \distantnative\Retour\Routes
     */
    protected $routes = null;

    /**
     * Returns either an existing instance or
     * creates a new instance and returns it
     *
     * @return \distantnative\Retour\Retour
     */
    public static function instance(): Retour
    {
        return static::$instance ?? static::$instance = new Retour;
    }

    /**
     * @return \distantnative\Retour\Log
     */
    public function log()
    {
        return $this->log ?? $this->log = new Log;
    }

    /**
     * @return \distantnative\Retour\Routes
     */
    public function routes()
    {
        return $this->routes ?? $this->routes = new Routes;
    }

    /**
     * Return information for Panel
     *
     * @return array
     */
    public static function info(): array
    {
        $plugin = kirby()->plugin('distantnative/retour');

        return [
            'deleteAfter' => option('distantnative.retour.deleteAfter'),
            'headers'     => Header::$codes,
            'hasLog'      => option('distantnative.retour.logs'),
            // 'release'     => $release = static::release(),
            'version'     => $version = $plugin->version(),
            // 'update'      => version_compare($version, $release)
        ];
    }

    protected static function release(bool $force = false): string
    {
        $kirby  = kirby();
        $option = $kirby->option('update.kirby') ?? $kirby->option('update');

        if ($force === true || $option !== false) {
            $cache  = $kirby->cache('retour');
            $cached = $cache->get('release');

            if ($cached === null) {
                $url = 'https://getkirby.com/plugins/distantnative/retour.json';
                $response = Remote::get($url)->json();
                $cached = $response['version'];
                $cache->set('release', $cached, 60 * 24);
            }

            return $cached;
        }
    }
}
