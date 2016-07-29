<?php

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Nine\Loaders\ConfigurationSet;
use Nine\Loaders\Configurator;
use Nine\Loaders\Support\LoaderReflector;

class BladeConfigurator extends Configurator
{

    /**
     * Entry Method for configuration.
     *
     * @return void
     */
    public function configure()
    {

    }

    /**
     * @param string           $name
     * @param ConfigurationSet $set
     * @param LoaderReflector  $reflector
     *
     * @internal param SymbolTable $st
     */
    public function preload(string $name, ConfigurationSet $set, LoaderReflector $reflector)
    {
        //ddump(func_get_args());
    }

}
