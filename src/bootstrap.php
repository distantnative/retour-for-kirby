<?php

$dir = __DIR__ . '/Retour';
load([
    'Kirby\\Retour\\Config'      => $dir . '/Config.php',
    'Kirby\\Retour\\LogDisabled' => $dir . '/LogDisabled.php',
    'Kirby\\Retour\\Log'         => $dir . '/Log.php',
    'Kirby\\Retour\\Redirect'    => $dir . '/Redirect.php',
    'Kirby\\Retour\\Redirects'   => $dir . '/Redirects.php',
    'Kirby\\Retour\\Timespan'    => $dir . '/Timespan.php',

    'Kirby\\Retour\\Panel\\TimespanDialog' => $dir . '/Panel/TimespanDialog.php',
    'Kirby\\Retour\\Panel\\RedirectDrawer' => $dir . '/Panel/RedirectDrawer.php',
    'Kirby\\Retour\\Panel\\RedirectCreateDrawer' => $dir . '/Panel/RedirectCreateDrawer.php',
    'Kirby\\Retour\\Panel\\RedirectEditDrawer' => $dir . '/Panel/RedirectEditDrawer.php',
    'Kirby\\Retour\\Panel\\FailureResolveDrawer' => $dir . '/Panel/FailureResolveDrawer.php',
    'Kirby\\Retour\\Panel\\View'       => $dir . '/Panel/View.php',

    'Kirby\\Retour\\Retour'      => $dir . '/Retour.php'
]);
