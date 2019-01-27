<?php

namespace distantnative\Retour;

use Kirby\Toolkit\Collection;

class Redirects
{

    protected $retour;
    protected $data;

    public static $field = 'retour';

    public function __construct($retour)
    {
        $this->retour = $retour;
        $this->data();
    }

    public function hit(string $pattern, string $path)
    {
        if ($redirect = $this->data->findBy('from', $pattern)) {
            $data          = $redirect->toArray();
            $data['hits']  = ($data['hits'] ?? 0) + 1;
            $data['last']  = date('Y-m-d H:i');

            $this->data->set($redirect->id(), $data);
            $this->data($this->data->toArray());
            $this->retour->errors()->log($path, true);
        }
    }

    public function data(array $data = null)
    {
        if (is_null($data) === false) {
            site()->update([static::$field => $data]);
        }

        return $this->data = site()->{static::$field}()->toStructure();
    }

    public function routes(): array
    {
        return $this->data
                    ->filterBy('status', '!=', 'disabled')
                    ->toArray(function ($route)  {
                        return [
                            'pattern' => $route->from(),
                            'action'  => function () use ($route) {
                                go($route->to(), $route->status()->toInt());
                            }
                        ];
                    });
    }

}
