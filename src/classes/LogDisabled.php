<?php

namespace distantnative\Retour;

/**
 * LogDisabled
 * Dummy class for when the log feature is disabled
 * to safely make calls to the log instance that will
 * be ignored.
 *
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 *
 * @codeCoverageIgnore
 */
class LogDisabled
{
    /**
     * Magic caller that blocks any call to the instance
     */
    public function __call(string $name, array $args): bool
    {
        return false;
    }
}
