<?php

$retour->redirects([
    [
        'from'   => 'episodes/an-old-podcast',
        'to'     => 'episodes/new-episode',
        'status' => '307',
        'hits'   => 423,
        'last'   => '2019-01-20 15:34'
    ],
    [
        'from'   => 'notes/(:all)',
        'to'     => 'blog//$1',
        'status' => '301',
        'hits'   => 342,
        'last'   => '2018-08-20 15:34'
    ],
    [
        'from'   => 'about/sara-salvador',
        'to'     => 'about/sara-rodriguez',
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


$errors = [];
$paths	= [
    'episodes/an-old-podcast',
    'blog/not-really-a-post',
    'events/did-not-happen',
    'events/0815',
    'this/is/not/a/page',
    'what/are/you/looking-for',
    'wp-login.php',
    'wp-admin',
    'contact/repressio-2n',
    'there-she-goes-2',
    'there-she-goes-again-2',
    'events/0815-2',
    'this/is/not/a/page-2',
    'what/are/you/looking-for-2',
    'wp-login.php-2',
    'wp-admin-2',
    'contact/repression-2',
    'there-she-goes-2',
    'there-she-goes-again-2'
];
$referrers = [
    null,
    null,
    null,
    null,
    null,
    'https://google.com',
    'https://getkirby.com',
    'https://producthunt.com/kirby-3-rockz'
];
$status = ['redirect', 'fail', 'fail', 'fail'];

for ($i=0; $i < 10000; $i++) {
    $errors[] = [
        'path'      => $paths[array_rand($paths)],
        'referrer'  => $referrers[array_rand($referrers)],
        'status'    => $status[array_rand($status)],
        'date'      => date("Y-m-d H:i", mt_rand(strtotime('2019-01-01'), strtotime('2019-02-01')))
    ];
}

$retour->log()->data($errors);
