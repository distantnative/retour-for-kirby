<?php

namespace distantnative\Retour;

use Kirby\Http\Header;

class Retour
{
    /**
     * @var \distantnative\Retour\Retour
     */
    protected static $instance = null;


    /**
     * @var \distantnative\Retour\Logs
     */
    protected $logs = null;

    /**
     * @var \distantnative\Retour\Redirects
     */
    protected $redirects = null;

    /**
     *
     * @return \distantnative\Retour\Retour
     */
    public static function instance(): Retour
    {
        if (static::$instance !== null) {
            return static::$instance;
        }

        return static::$instance = new Retour;
    }

    /**
     *
     * @return \distantnative\Retour\Logs
     */
    public function logs(): Logs
    {
        return $this->logs ?? $this->logs = new Logs;
    }

    /**
     *
     * @return \distantnative\Retour\Redirects
     */
    public function redirects(): Redirects
    {
        return $this->redirects ?? $this->redirects = new Redirects;
    }

    /**
     * Return information for Panel
     *
     * @return array
     */
    public static function info(): array
    {
        return [
            'headers'     => Header::$codes,
            'logs'        => option('distantnative.retour.logs'),
            'deleteAfter' => option('distantnative.retour.deleteAfter')
        ];
    }
}
