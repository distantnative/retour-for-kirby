<?php

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

$translations= [];
$root = __DIR__ . '/i18n';

foreach (Dir::files($root) as $file) {
    $locale = basename($file, '.json');
    $content = F::read($root . '/' . $file);
    $translations[] = json_decode($content, true);
}

return $translations;
