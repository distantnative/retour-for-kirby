<?php

use Kirby\Data\Data;
use Kirby\Filesystem\Dir;

$translations = [];
$root         = dirname(__DIR__) . '/i18n';

foreach (Dir::files($root) as $file) {
	$translations[basename($file, '.json')] = Data::read($root . '/' . $file);
}

return $translations;
