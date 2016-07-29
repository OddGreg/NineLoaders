<?php namespace Nine\Loaders\Interfaces;

use Nine\Loaders\ConfigFileReader;

/**
 * @package Nine Loaders
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
interface ConfiguratorInterface
{
    /**
     * Entry Method for configuration.
     *
     * @return void
     */
    public function configure();

}
