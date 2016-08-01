<?php namespace Nine\Loaders\Interfaces;

use Nine\Loaders\Configurators\Interfaces\ConfiguratorInterface;

/**
 * @package Nine Loaders
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

interface LoaderInterface
{
    /**
     * Activates a loaded configuration.
     *
     * @return void
     */
    public function activate();

    /**
     * Load a configurator and trigger the configure() method.
     *
     * @param ConfiguratorInterface $configurator
     *
     * @return void
     */
    public function load(ConfiguratorInterface $configurator);
}
