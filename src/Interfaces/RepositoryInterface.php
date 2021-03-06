<?php declare(strict_types = 1);

namespace Nine\Loaders\Interfaces;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Nine\Loaders\Configurator;

interface RepositoryInterface
{
    /**
     * Get (or simply `get`) a configurator based on the key.
     *
     * @param string $key        The configurator ID.
     * @param array  $parameters Optional parameters to pass to the configurator;
     *
     * @return Configurator|ConfiguratorInterface
     */
    public function get(string $key, array $parameters = []) : Configurator;

    /**
     * Set a configurator to a key.
     *
     * @param Configurator|ConfiguratorInterface $configurator
     *
     * @return
     * @internal param string $key
     * @internal param int $priority
     */
    public function insert(Configurator $configurator);
}
