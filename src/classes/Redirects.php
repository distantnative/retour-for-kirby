<?php

namespace distantnative\Retour;

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
     * Plugin instance
     *
     * @var \distantnative\Retour\Plugin
     */
    protected $plugin;

    /**
     * Class constructor
     *
     * @param \distantnative\Retour\Plugin $plugin Plugin instance
     * @param array $redirects Array of redirects
     */
    public function __construct(Plugin $plugin, array $redirects)
    {
        $this->plugin = $plugin;
        parent::__construct($redirects);
    }

    /**
     * Creates new redirect
     *
     * @param array|null $data
     * @return self
     */
    public function create(?array $data = null): self
    {
        $data   ??= static::fromQuery();
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
     *
     * @param \distantnative\Retour\Plugin $plugin Plugin instance
     * @param array $config
     * @return self
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
     * Gets form data from query for redirect
     *
     * @return array
     */
    public static function fromQuery(): array
    {
        return [
            'from'      => get('from'),
            'to'        => get('to'),
            'status'    => get('status'),
            'priority'  => get('priority'),
            'comment'   => get('comment')
        ];
    }

    /**
     * Returns the Plugin instance
     *
     * @return \distantnative\Retour\Plugin
     */
    public function plugin(): Plugin
    {
        return $this->plugin;
    }

    /**
     * Writes collection to config file
     *
     * @return void
     */
    public function save(): void
    {
        Config::set('redirects', $this->toArray());
    }

    /**
     * Turns collection into array, by default turning
     * Redirect objects into array as well
     *
     * @param Closure $map
     * @return array
     */
    public function toArray(Closure $map = null): array
    {
        $map ??= fn (Redirect $redirect) => $redirect->toArray();
        $array = parent::toArray($map);
        return array_values($array);
    }

    /**
     * Returns redirects data combined with log data
     *
     * @param string $from
     * @param string $to
     * @return array
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
     *
     * @param bool $priority
     * @return array
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
     * @param array|null $data
     * @return self
     */
    public function update(string $id, ?array $data = null): self
    {
        $data   ??= static::fromQuery();
        $redirect = new Redirect($data);
        $this->set($id, $redirect);
        return $this;
    }
}
