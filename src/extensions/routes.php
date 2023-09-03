<?php

namespace Kirby\Retour;

/**
 * Sets up priority redirects as routes
 *
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */

return fn (): array => Retour::instance()->redirects()->toRoutes(true);
