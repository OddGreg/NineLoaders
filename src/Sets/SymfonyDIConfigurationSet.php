<?php namespace Nine\Loaders\Sets;

use Nine\Loaders\ConfigurationSet;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

class SymfonyDIConfigurationSet extends ConfigurationSet
{
    /** @var ContainerBuilder */
    protected $container;

    public function setContainer(ContainerBuilder $container)
    {
        $this->container = $container;

        return $this;
    }

}
