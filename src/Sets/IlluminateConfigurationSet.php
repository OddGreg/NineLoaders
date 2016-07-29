<?php namespace Nine\Loaders\Sets;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Illuminate\Container\Container;
use Nine\Loaders\ConfigurationSet;

class IlluminateConfigurationSet extends ConfigurationSet
{
    /** @var Container */
    protected $container;

    public function setContainer(Container $container)
    {
        $this->container = $container;
        $this->container->setInstance($container);

        return $this;
    }

}
