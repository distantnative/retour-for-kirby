<?php

namespace Kirby\Retour;

use Closure;
use Kirby\Cms\Collection;
use Kirby\Exception\DuplicateException;

/**
 * Redirects
 * Collection of all configured redirect routes
 *
 * @package   Retour for Kirby
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://github.com/distantnative/retour-for-kirby
 * @copyright Nico Hoffmann
 * @license   https://opensource.org/licenses/MIT
 */
class Redirects extends Collection
{
    /**
     * @param array $redirects Array of redirects
     */
    public function __construct(
        protected Retour $retour,
        array $redirects
    )
    {
        parent::__construct($redirects);
    }

    /**
     * Creates new redirect
     */
    public function create(array $data): self
    {
        $redirect = new Redirect($data);

        if ($this->has($redirect->id()) === true) {
            throw new DuplicateException('Redirect with ID already exists');
        }

        $this->prepend($redirect->id(), $redirect);
        return $this;
    }

    /**
     * Takes a config array and turns it into
     * a collection of redirect objects
     */
    public static function factory(Retour $retour, array $config): self
    {
        $redirects = new self($retour, []);

        foreach ($config as $data) {
            $redirect = new Redirect($data);
            $redirects->append($redirect->id(), $redirect);
        }

        return $redirects;
    }

    public function retour(): Retour
    {
        return $this->retour;
    }

    /**
     * Writes collection to config file
     */
    public function save(): void
    {
        $config = $this->retour()->config();
        $config->write('redirects', $this->toArray());
    }

    /**
     * Turns collection into array, by default turning
     * Redirect objects into array as well
     */
    public function toArray(Closure $map = null): array
    {
        $map ??= fn (Redirect $redirect) => $redirect->toArray();
        $array = parent::toArray($map);
        return array_values($array);
    }

    /**
     * Returns redirects data combined with log data
     */
    public function toData(string $from, string $to): array
    {
        $retour = $this->retour();

        // If logging is disabled, return without data
        if ($retour->hasLog() === false) {
            return $this->toArray();
        }

        return $this->toArray(function (Redirect $redirect) use ($retour, $from, $to): array {
            $data = $redirect->toArray();
            /** @var array */
            [
                'hits' => $data['hits'],
                'last' => $data['last']
            ]  = $retour->log()->redirect($data['from'], $from, $to);
            return $data;
        });
    }

    /**
     * Returns routes config for all active redirects
     */
    public function toRoutes(bool $priority = false): array
    {
        // Filter: no routes for disabled redirects
        $redirects = $this->filter(
            fn (Redirect $redirect): bool =>
                $redirect->isActive() === true &&
                $redirect->priority() === $priority
        );

        // create route array for each redirect
        return $redirects->toArray(
            fn (Redirect $route) => $route->toRoute()
        );
    }

    /**
     * Updates existing redirect
     *
     * @param string $id ID of exisiting redirect
     */
    public function update(string $id, array $data): self
    {
        $redirect = new Redirect($data);
        $this->set($id, $redirect);
        return $this;
    }
}
