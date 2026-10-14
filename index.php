<?php

/**
 * Retour for Kirby Plugin
 *
 * Easily add and manage redirects from the
 * Kirby Panel and track 404 errors
 *
 * @link      https://github.com/distantnative/retour-for-kirby
 * @license   https://opensource.org/licenses/MIT
 */

use Composer\Semver\Semver;
use Kirby\Cms\App as Kirby;

// validate Kirby version
if (Semver::satisfies(Kirby::version() ?? '0.0.0', '~6.0') === false) {
	throw new Exception('Retour requires Kirby 6');
}

// load classes
require_once __DIR__ . '/src/bootstrap.php';

// register the plugin
Kirby::plugin('distantnative/retour', [
	'areas'        => require_once __DIR__ . '/src/extensions/areas.php',
	'commands'     => require_once __DIR__ . '/src/extensions/commands.php',
	'hooks'        => require_once __DIR__ . '/src/extensions/hooks.php',
	'routes'       => require_once __DIR__ . '/src/extensions/routes.php',
	'translations' => require_once __DIR__ . '/src/extensions/i18n.php'
]);
