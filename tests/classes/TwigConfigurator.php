<?php

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Nine\Loaders\Configurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigConfigurator extends Configurator
{
    /**
     * Entry Method for configuration.
     *
     * @param ContainerBuilder $container
     */
    public function apply(ContainerBuilder $container)
    {
        //expose($container);
    }

}
