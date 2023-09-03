<?php

/**
 * Retour for Kirby Plugin
 *
 * Easily add and manage redirects from the
 * Kirby 4 Panel and track 404 errors
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
    version_compare(Kirby::version() ?? '0.0.0', '4.0.0-alpha.7', '<') === true ||
    version_compare(Kirby::version() ?? '0.0.0', '5.0.0', '>=') === true
) {
    throw new Exception('Retour 5 requires Kirby 4');
}

// load classes
require_once 'src/bootstrap.php';

// register the plugin
Kirby::plugin('distantnative/retour', [
    'areas'        => require_once 'src/extensions/areas.php',
    'hooks'        => require_once 'src/extensions/hooks.php',
    'routes'       => require_once 'src/extensions/routes.php',
    'translations' => require_once 'src/extensions/i18n.php'
]);
