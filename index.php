<?php

/**
 * Retour for Kirby Plugin
 *
 * Easily add and manage redirects from the
 * Kirby 3 Panel and track 404 errors
 *
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */

use Kirby\Cms\App as Kirby;

// validate Kirby version
if (
    version_compare(Kirby::version() ?? '0.0.0', '3.7.4', '<') === true ||
    version_compare(Kirby::version() ?? '0.0.0', '3.10.0', '>=') === true
) {
    throw new Exception('Retour 4.4.0 supports Kirby 3.7.4 to 3.9.x');
}

// autoload classes
$classes = __DIR__ . '/src/classes';
load([
    'distantnative\\Retour\\Config'      => $classes . '/Config.php',
    'distantnative\\Retour\\LogDisabled' => $classes . '/LogDisabled.php',
    'distantnative\\Retour\\Log'         => $classes . '/Log.php',
    'distantnative\\Retour\\Redirect'    => $classes . '/Redirect.php',
    'distantnative\\Retour\\Redirects'   => $classes . '/Redirects.php',
    'distantnative\\Retour\\Panel'       => $classes . '/Panel.php',
    'distantnative\\Retour\\Plugin'      => $classes . '/Plugin.php'
]);

// register the plugin
Kirby::plugin('distantnative/retour', [
    'areas'        => require 'src/extensions/areas.php',
    'hooks'        => require 'src/extensions/hooks.php',
    'routes'       => require 'src/extensions/routes.php',
    'translations' => require 'src/extensions/i18n.php'
]);
