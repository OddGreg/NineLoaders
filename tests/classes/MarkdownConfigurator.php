<?php

use Nine\Loaders\ConfigFileReader;
use Nine\Loaders\Configurator;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */


class MarkdownConfigurator extends Configurator
{
    /**
     * Entry Method for configuration.
     *
     * @return void
     */
    public function apply()
    {
        // TODO: Implement configure() method.
    }

    /**
     * @param ConfigFileReader $config
     *
     * @return void
     */
    public function load(ConfigFileReader $config)
    {
            $this->settings = $config['view.markdown'];
    }
}
