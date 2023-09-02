<?php

namespace distantnative\Retour;

use Closure;
use Kirby\Cms\App;
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
     * Class constructor
     *
     * @param array $redirects Array of redirects
     */
    public function __construct(
        protected Plugin $plugin,
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
    public static function factory(Plugin $plugin, array $config): self
    {
        $redirects = new self($plugin, []);

        foreach ($config as $data) {
            $redirect = new Redirect($data);
            $redirects->append($redirect->id(), $redirect);
        }

        return $redirects;
    }

    /**
     * Returns the Plugin instance
     */
    public function plugin(): Plugin
    {
        return $this->plugin;
    }

    /**
     * Writes collection to config file
     */
    public function save(): void
    {
        Config::set('redirects', $this->toArray());
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
        $retour = $this->plugin();

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
        $redirects = $this->filter(fn (Redirect $redirect): bool => $redirect->isActive() === true &&
            $redirect->priority() === $priority);

        // create route array for each redirect
        return $redirects->toArray(fn (Redirect $route) => $route->toRoute());
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
