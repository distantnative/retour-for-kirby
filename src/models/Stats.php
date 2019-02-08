<?php

namespace distantnative\Retour;

class Stats extends Store
{

    public function __construct()
    {
        $this->file = kirby()->root('site') . '/logs/retour/{x}.stats';
    }

    protected static function defaults(): array
    {
        return [
            'day'   => [],
            'week'  => [],
            'month' => [],
        ];
    }

    public function count(array $tmp): void
    {
        $stats = [];

        foreach ($tmp as $item) {
            $time = strtotime($item['date']);
            $stats[date('Y-m', $time)][] = $item;
        }

        foreach ($stats as $year => $items) {
            $data = $this->data($year);

            foreach ($items as $item) {
                $time = strtotime($item['date']);
                $structure = [
                    'day'   => [
                        'group' => date('Y-m-d', $time),
                        'key'   => date('Y-m-d H:', $time)
                    ],
                    'month' => [
                        'group' => date('Y-m', $time),
                        'key'   => date('Y-m-d', $time)
                    ]
                ];

                $type = $item['isFail'] ? 'fails' : 'redirects';

                foreach ($structure as $by => $id) {
                    if (isset($data[$by][$id['group']]) === false) {
                        $data[$by][$id['group']] = [];
                    }

                    if (isset($data[$by][$id['group']][$id['key']]) === false) {
                        $data[$by][$id['group']][$id['key']] = [
                            'fails'     => 0,
                            'redirects' => 0
                        ];
                    }

                    $data[$by][$id['group']][$id['key']][$type]++;
                }
            }

            $this->write($data, $year);
        }
    }

    public function get(string $by, int $offset = 0): array
    {
        switch ($by) {
            case 'day':
                $step  = 60 * 60;
                $start = strtotime(date('Y-m-d 00:00') . $offset . ' day');
                $end   = strtotime(date('Y-m-d 23:59') . $offset . ' day');
                $group = 'Y-m-d';
                $key   = 'Y-m-d H:';
                $label = 'G:00';
                break;

            case 'week':
                $step    = 60 * 60 * 24;
                $start   = strtotime(date('Y-m-d ', strtotime('Monday this week')) . $offset . ' week');
                $end     = strtotime(date('Y-m-d ', strtotime('Sunday this week')) . $offset . ' week');
                $group   = 'Y-m';
                $key     = 'Y-m-d';
                $label   = 'l';
                $by      = 'month';
                break;

            case 'month':
                $step    = 60 * 60 * 24;
                $start   = strtotime(date('Y-m-01 ') . $offset . ' month');
                $end     = strtotime(date('Y-m-t', $start));
                $group   = 'Y-m';
                $key     = 'Y-m-d';
                $label   = 'j';
                break;
        }

        $result = [
            'headline'  => static::headline($start, $end),
            'labels'    => [],
            'fails'     => [],
            'redirects' => [],
        ];

        for ($time = $start; $time <= $end; $time += $step) {
            $data                  = $this->data(date('Y-m', $time));
            $result['labels'][]    = date($label, $time);
            $result['fails'][]     = $data[$by][date($group, $time)][date($key, $time)]['fails'] ?? 0;
            $result['redirects'][] = $data[$by][date($group, $time)][date($key, $time)]['redirects'] ?? 0;
        }

        return $result;
    }

    protected function headline($start, $end): string
    {
        // whole day
        if (date('Y-m-d', $start) === date('Y-m-d', $end)) {
            return date('j F Y', $end);
        }

        // whole month
        if (
            date('Y-m', $start) === date('Y-m', $end) &&
            date('j', $start) === '1' &&
            date('j', $end) === date('t', $end)
        ) {
            return date('F Y', $end);
        }

        // days, same month
        if (date('m', $start) === date('m', $end)) {
            return date('j', $start) . '-' . date('j F Y', $end);
        }

        // detailed date
        if (date('Y', $start) === date('Y', $end)) {
           return date('j F', $start) . ' - ' . date('j F Y', $end);
        }

        return date('j F Y', $start) . ' - ' . date('j F Y', $end);
    }
}

