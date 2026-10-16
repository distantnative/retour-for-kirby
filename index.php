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
use Kirby\Retour\Commands\Indices;
use Kirby\Retour\Commands\Seed;
use Kirby\Retour\Retour;

// validate Kirby version
if (Semver::satisfies(Kirby::version() ?? '0.0.0', '~6.0') === false) {
	throw new Exception('Retour requires Kirby 6');
}

// load classes
require_once __DIR__ . '/src/bootstrap.php';

// register the plugin
Kirby::plugin('distantnative/retour', [
	'areas'        => require_once __DIR__ . '/src/extensions/areas.php',
	'commands'     => [
		'retour:indices' => Indices::class,
		'retour:seed'    => Seed::class
	],
	'hooks'        => require_once __DIR__ . '/src/extensions/hooks.php',
	'options'      => [
		'config'        => fn () => Kirby::instance()->root('config') . '/retour.yml',
		'database'      => fn () => Kirby::instance()->root('logs') . '/retour/log.sqlite',
		'deleteAfter'   => false,
		'ignore'        => [],
		'logs'          => true,
		'preserveQuery' => false,
		'site'          => true,
		'status'        => null
	],
	'routes'       => fn (): array => Retour::instance()->redirects()->toRoutes(true),
	'translations' => require_once __DIR__ . '/src/extensions/i18n.php'
]);
