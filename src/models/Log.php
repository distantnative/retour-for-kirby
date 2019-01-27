<?php

namespace distantnative\Retour;

use Kirby\Data\Data;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;

class Log {

    protected $retour;
    protected $data;

    public static $file = 'content/retour.log';

    public function __construct($retour)
    {
        $this->retour = $retour;
        $this->data();
    }

    protected function caching(): void
    {
        $this->fails();
        $this->retour->stats()->get('month');
        $this->retour->stats()->get('week');
        $this->retour->stats()->get('day');
    }

    public function data(array $data = null)
    {
        $file = kirby()->root('index') . '/' . static::$file;

        if (is_null($data) === false) {
            Data::write($file, $data, 'yaml');
            $this->caching();
        }

        $data = F::exists($file) ? Data::read($file, 'yaml'): [];

        return $this->data = new Collection($data);
    }

    protected function group(): Collection
    {
        $list = new Collection([]);

        foreach ($this->data as $error) {
            $id   = $error['path'] . '$' . $error['referrer'];
            $fail = $error['status'] === 'fail';

            if (isset($list->data[$id]) === false) {
                $error['fails']     = $fail ? 1 : 0;
                $error['redirects'] = $fail ? 0 : 1;
                $list->data[$id]    = $error;

            } else {
                $data = $list->data[$id];
                $data[$fail ? 'fails' : 'redirects']++;

                if (strtotime($error['date']) > strtotime($data['date'])) {
                    $data['date'] = $error['date'];
                }

                $list->data[$id] = $data;
            }
        }

        return $list;
    }

    public function fails(string $sort = 'fails'): array
    {
        if ($cached = $this->retour->cache()->get('fails')) {
            return $cached;
        }

        $fails = $this->group()->filterBy('fails', '!=', 0);
        $fails = $fails->sortBy($sort, 'desc');
        $fails = array_values($fails->toArray());

        $this->retour->cache()->set('fails', $fails);

        return $fails;
    }

    public function add(string $path, bool $fail = true): void
    {
        $this->data($this->data->append([
            'path'     => $path,
            'referrer' => $_SERVER['HTTP_REFERER'] ?? null,
            'status'   => $fail ? 'fail' : 'redirect',
            'date'     => date('Y-m-d H:i')
        ])->toArray());
    }

}
