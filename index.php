<?php

/**
 * Retour for Kirby Plugin
 *
 * @link      https://github.com/distantnative/retour-for-kirby
 * @license   https://opensource.org/licenses/MIT
 */

use Kirby\Cms\App as Kirby;
use Kirby\Retour\Commands\Seed;

$version = Kirby::version() ?? '0.0.0';

if (
	version_compare($version, '6.0.0-dev', '<') === true ||
	version_compare($version, '7.0.0-dev', '>=') === true
) {
	throw new Exception('Retour requires Kirby 6');
}

require_once __DIR__ . '/src/bootstrap.php';

Kirby::plugin('distantnative/retour', [
	'areas'        => require __DIR__ . '/src/extensions/areas.php',
	'commands'     => [
		'retour:seed' => Seed::class
	],
	'hooks'        => require __DIR__ . '/src/extensions/hooks.php',
	'options'      => [
		'cache'         => true,
		'config'        => fn () => Kirby::instance()->root('config') . '/retour.yml',
		'database'      => fn () => Kirby::instance()->root('logs') . '/retour/log.sqlite',
		'deleteAfter'   => 12,
		'ignore'        => [],
		'ignoreIps'     => [],
		'logs'          => true,
		'preserveQuery' => false,
		'site'          => true,
		'status'        => null
	],
	'routes'       => require __DIR__ . '/src/extensions/routes.php',
	'translations' => require __DIR__ . '/src/extensions/i18n.php'
]);
