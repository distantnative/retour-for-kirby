<?php

namespace Kirby\Retour;

/**
 * Dummy class for when the log feature is disabled
 * to safely make calls to the log instance that will
 * be ignored.
 *
 * @codeCoverageIgnore
 */
class LogDisabled
{
	/**
	 * Magic caller that blocks any call to the instance
	 */
	public function __call(string $name, array $args): mixed
	{
		return null;
	}
}
