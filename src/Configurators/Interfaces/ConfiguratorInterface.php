<?php namespace Nine\Loaders\Configurators\Interfaces;

use Nine\Loaders\ConfigFileReader;

/**
 * @package Nine Loaders
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
interface ConfiguratorInterface
{
    /**
     * @param ConfigFileReader $reader
     *
     * @return void
     */
    public function load(ConfigFileReader $reader);

}
