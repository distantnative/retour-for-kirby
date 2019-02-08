<?php

$retour->redirects()->write([
    [
        'from'    => 'episodes/an-old-podcast',
        'to'      => 'episodes/new-episode',
        'status'  => '307',
        'hits'    => 423,
        'last'    => '2019-01-20 15:34'
    ],
    [
        'from'   => 'notes/(:all)',
        'to'     => 'blog//$1',
        'status' => '301',
        'hits'   => 342,
        'last'   => '2018-08-20 15:34'
    ],
    [
        'from'   => 'salvador',
        'to'     => '',
        'status' => 'disabled',
        'hits'   => 0,
        'last'   => null
    ],
    [
        'from'   => 'shop/(:any)/boots/(:all)',
        'to'     => 'shop/$1/shoes/$2',
        'status' => '302',
        'hits'   => 32,
        'last'   => '2019-01-02 15:34'
    ]
]);

$log = [];

for ($i=0; $i < 5000; $i++) {
    $path     = Str::random(6) . '/' . Str::random(6);
    $referrer = mt_rand(0, 1) ? 'https://' . Str::random(6) : null;
    $id       = $path . '$' . $referrer;
    $log[$id] = [
        'path'      => $path,
        'referrer'  => $referrer,
        'fails'     => $fails = mt_rand(0, 100) * mt_rand(0, 100),
        'redirects' => (int)($fails/mt_rand(1, 15)),
        'last'      => date("Y-m-d H:i", mt_rand(strtotime('2014-01-01'), time()))
    ];
}

$retour->log()->write($log);


$stati    = ['redirect', 'fail', 'fail', 'fail'];
$monthday = [31,28,31,30,31,30,31,31,30,31,30,31];

for ($year=2016; $year <= 2019; $year++) {

    for ($month=1; $month <= 12; $month++) {
        $stats = [
            'day'   => [],
            'month' => [],
        ];

        for ($i=0; $i < 5000; $i++) {
            $by   = array_keys($stats)[array_rand(array_keys($stats))];
            $type = $stati[array_rand($stati)] . 's';
            $time = mt_rand(strtotime($year . '-'.$month.'-01 00:00'), strtotime($year . '-'.$month.'-'.$monthday[$month-1] . ' 23:59'));
            $structure = [
                'day'   => ['group' => date('Y-m-d', $time), 'key' => date('Y-m-d H:', $time)],
                'month' => ['group' => date('Y-m', $time),   'key' => date('Y-m-d', $time)]
            ];

            foreach ($structure as $by => $id) {
                if (isset($stats[$by][$id['group']]) === false) {
                    $stats[$by][$id['group']] = [];
                }

                if (isset($stats[$by][$id['group']][$id['key']]) === false) {
                    $stats[$by][$id['group']][$id['key']] = [
                        'fails'     => 0,
                        'redirects' => 0
                    ];
                }

                $stats[$by][$id['group']][$id['key']][$type]++;
            }
        }


        $retour->stats()->write(
            $stats,
            $year.'-'.str_pad($month,2,'0',STR_PAD_LEFT)
        );
    }
}


