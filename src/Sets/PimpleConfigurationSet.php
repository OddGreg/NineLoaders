<?php namespace Nine\Loaders\Sets;

use Nine\Loaders\ConfigurationSet;
use Pimple\Container;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
class PimpleConfigurationSet extends ConfigurationSet
{

    /** @var Container */
    protected $container;

    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }

}
