<?php

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Nine\Loaders\ConfigFileReader;
use Nine\Loaders\Configurator;

class TestConfigurator extends Configurator
{
    /**
     * Entry Method
     *
     * @internal param array $parameters Optional configuration parameters.
     */
    public function apply()
    {
        // here is where the Configurator acts on any parameters provided.
    }

    /**
     * @param ConfigFileReader $config
     *
     * @return void
     */
    public function load(ConfigFileReader $config)
    {
        //$this->settings = $config['test'];
    }
}
