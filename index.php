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
if (version_compare(Kirby::version() ?? '0.0.0', '3.6.0-rc.1', '<') === true) {
    throw new Exception('Retour requires at least Kirby 3.6.0-rc.1');
}

// autoload classes
load([
    'distantnative\\Retour\\Config'      => __DIR__ . '/src/classes/Config.php',
    'distantnative\\Retour\\LogDisabled' => __DIR__ . '/src/classes/LogDisabled.php',
    'distantnative\\Retour\\Log'         => __DIR__ . '/src/classes/Log.php',
    'distantnative\\Retour\\Redirect'    => __DIR__ . '/src/classes/Redirect.php',
    'distantnative\\Retour\\Redirects'   => __DIR__ . '/src/classes/Redirects.php',
    'distantnative\\Retour\\Panel'       => __DIR__ . '/src/classes/Panel.php',
    'distantnative\\Retour\\Plugin'      => __DIR__ . '/src/classes/Plugin.php'
]);

// register the plugin
Kirby::plugin('distantnative/retour', [
    'api'          => require 'src/config/api.php',
    'areas'        => require 'src/config/areas.php',
    'hooks'        => require 'src/config/hooks.php',
    'routes'       => require 'src/config/routes.php',
    'translations' => require 'src/config/i18n.php'
]);
