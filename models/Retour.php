<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

class Retour
{
    protected $logs;
    protected $redirects;
    protected $stats;
    protected $system;

    public static $root = '/logs/retour';

    public function flush(): void
    {
        Dir::remove(kirby()->root('site') . static::$root);
    }

    public function logs(): Logs
    {
        return $this->log = $this->logs ?? new Logs;
    }

    public function process()
    {
        $tmp = $this->temporaries();

        if (empty($tmp) === false) {
            $redirects = array_filter($tmp, function ($x) {
                return $x['isFail'] === false;
            });

            $this->logs()->add($tmp);
            $this->stats()->count($tmp);
            $this->redirects()->hit($redirects);
        }
    }

    public function redirects(): Redirects
    {
        return $this->redirects = $this->redirects ?? new Redirects;
    }

    public function stats(): Stats
    {
        return $this->stats = $this->stats ?? new Stats;
    }

    public static function store(string $path, bool $isFail, string $pattern = null)
    {
        $root = kirby()->root('site') . static::$root;
        $file = $root . '/' . md5($path) . '.' . time() . '.tmp';

        Data::write($file, [
            'path'     => $path,
            'referrer' => $_SERVER['HTTP_REFERER'] ?? null,
            'isFail'   => $isFail,
            'pattern'  => $pattern,
            'date'     => date('Y-m-d H:i')
        ], 'yaml');
    }

    public function system(): System
    {
        return $this->system = $this->system ?? new System;
    }

    protected function temporaries(): array
    {
        $files = kirby()->root('site') . static::$root . '/*.tmp';
        $tmp   = [];

        foreach (glob($files) as $file) {
            $tmp[] = Data::read($file, 'yaml');
            F::remove($file);
        }

        return $tmp;
    }
}
