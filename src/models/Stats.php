<?php

namespace distantnative\Retour;

class Stats
{

    protected $retour;
    protected $logs;

    protected $step;
    protected $limit;
    protected $compare;
    protected $start;
    protected $end;
    protected $offset = 0;
    protected $unit = 'j';

    protected $labels    = [];
    protected $fails     = [];
    protected $redirects = [];

    public function __construct($retour)
    {
        $this->retour = $retour;
        $this->log    = $this->retour->log()->data();
    }

    protected function byMonth(): void
    {
        $this->step    = 60 * 60 * 24;
        $this->limit   = 'Y-m';
        $this->compare = 'Y-m-d ';
        $this->start   = strtotime(date('Y-m-01 ') . $this->offset . ' month');
        $this->end     = strtotime(date('Y-m-t', $this->start));
    }

    protected function byWeek(): void
    {
        $this->step    = 60 * 60 * 24;
        $this->limit   = 'Y-m';
        $this->compare = 'Y-m-d ';
        $this->start   = strtotime(date('Y-m-d ', strtotime('last Monday')) . $this->offset . ' week');
        $this->end     = $this->start + ($this->step * 6);
        $this->unit    = 'l';
    }

    protected function byDay(): void
    {
        $this->step    = 60 * 60;
        $this->limit   = 'Y-m-d';
        $this->compare = 'Y-m-d H:';
        $this->start   = strtotime(date('Y-m-d 0:00') . $this->offset . ' day');
        $this->end     = strtotime(date('Y-m-d 23:59') . $this->offset . ' day');
        $this->unit    = 'G:00';
    }

    public function get(string $by, int $offset = 0): array
    {
        if ($cached = $this->retour->cache()->get('stats.' . $by . '.' . $offset)) {
            return $cached;
        }

        $this->offset = $offset;
        $this->{'by' . ucfirst($by)}();
        $this->reduce();
        $this->populate();

        $data = [
            'headline'  => $this->headline(),
            'labels'    => $this->labels,
            'fails'     => $this->fails,
            'redirects' => $this->redirects,
        ];

        $this->retour->cache()->set('stats.' . $by, $data);

        return $data;
    }

    protected function headline(): string
    {
        // whole day
        if (date('Y-m-d', $this->start) === date('Y-m-d', $this->end)) {
            return date('j F Y', $this->end);
        }

        // whole month
        if (
            date('Y-m', $this->start) === date('Y-m', $this->end) &&
            date('j', $this->start) === '1' &&
            date('j', $this->end) === date('t', $this->end)
        ) {
            return date('F Y', $this->end);
        }

        // days, same month
        if (date('m', $this->start) === date('m', $this->end)) {
            return date('j', $this->start) . '-' . date('j F Y', $this->end);
        }

        // detailed date
        if (date('Y', $this->start) === date('Y', $this->end)) {
           return date('j F', $this->start) . ' - ' . date('j F Y', $this->end);
        }

        return date('j F Y', $this->start) . ' - ' . date('j F Y', $this->end);
    }

    protected function populate(): void
    {
        for ($time = $this->start; $time <= $this->end; $time += $this->step) {
            $this->labels[]    = date($this->unit, $time);

            $log = $this->log->filterBy(
                'date',
                '^=',
                date($this->compare, $time)
            );

            $fails = 0;
            $redirects = 0;

            foreach ($log as $item) {
                $item['status'] === 'fail' ? $fails++ : $redirects++;
            }

            $this->fails[]     = $fails;
            $this->redirects[] = $redirects;
        }
    }

    protected function reduce(): void
    {
        $this->data = $this->log->filterBy(
            'date',
            '^=',
            date($this->limit, $this->start)
        );
    }
}

