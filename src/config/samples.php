<?php

namespace distantnative\Retour;

function rand_date($min_date, $max_date) {
    $min_epoch = strtotime($min_date);
    $max_epoch = strtotime($max_date);

    $rand_epoch = rand($min_epoch, $max_epoch);

    return date('Y-m-d H:i:s', $rand_epoch);
}

$redirects = [
    [
        'status' => '301',
        'from'   => 'blog/(:any)/(:all)',
        'to'     => 'notes/$1/entries/$2'
    ],
    [
        'status' => '503',
        'from'   => 'podcast/this-one-episode',
        'to'     => null
    ],
    [
        'status' => '301',
        'from'   => 'test',
        'to'     => 'about'
    ],
    [
        'status' => 'disabled',
        'from'   => 'soon/to/cancel',
        'to'     => 'not/yet-ready'
    ]
];

$log = new Log;

Redirects::write($redirects);

foreach ($redirects as $redirect) {
    for ($i= rand(200, 1000); $i > 0; $i--) {
        $log->add([
            'date' => rand_date(date('Y-m-01', strtotime('-4 month')), date('Y-m-t')),
            'path' => $redirect['from'],
            'redirect' => $redirect['from']
        ]);
    }
}

$paths = [];
$referers = [];

for ($i = 10; $i > 0; $i--) {
    $paths[] = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, 10);
}

for ($i = 100; $i > 0; $i--) {
    $referers[] = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, 15);
}

for ($i = rand(1000, 3000); $i > 0; $i--) {
    $log->add([
        'date' => rand_date(date('Y-m-01', strtotime('-12 month')), date('Y-m-t')),
        'path' => $paths[array_rand($paths)],
        'referrer' => rand(1, 10) > 7 ? $referers[array_rand($referers)] : null
    ]);
}
