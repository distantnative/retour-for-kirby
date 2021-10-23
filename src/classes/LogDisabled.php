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
 */
class LogDisabled
{
    /**
     * Magic caller that blocks any call to the instance
     *
     * @param string $property
     * @param array $arguments
     * @return bool
     *
     * @codeCoverageIgnore
     */
    public function __call(string $property, array $arguments): bool
    {
        return false;
    }
}
