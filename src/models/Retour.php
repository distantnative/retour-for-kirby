<?php

namespace distantnative;

class Retour
{

    protected $log;
    protected $redirects;
    protected $stats;
    protected $system;

    protected $cache;

    public function cache() {
        return kirby()->cache('distantnative.retour');
    }

    public function log()
    {
        if ($this->log) {
            return $this->log;
        }

        return $this->log = new Retour\Log($this);
    }

    public function redirects()
    {
        if ($this->redirects) {
            return $this->redirects;
        }

        return $this->redirects = new Retour\Redirects($this);
    }

    public function stats()
    {
        if ($this->stats) {
            return $this->stats;
        }

        return $this->stats = new Retour\Stats($this);
    }

    public function system()
    {
        if ($this->system) {
            return $this->system;
        }

        return $this->system = new Retour\System($this);
    }

}
