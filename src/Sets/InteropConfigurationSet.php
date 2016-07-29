<?php namespace Nine\Loaders\Sets;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Interop\Container\ContainerInterface;
use Nine\Loaders\ConfigFileReader;
use Nine\Loaders\ConfigurationSet;

class InteropConfigurationSet extends ConfigurationSet
{
    /** @var ContainerInterface */
    protected $container;

    public function __construct(string $key, ConfigFileReader $reader, ContainerInterface $container)
    {
        parent::__construct($key, $reader);
        $this->container = $container;
    }

}
