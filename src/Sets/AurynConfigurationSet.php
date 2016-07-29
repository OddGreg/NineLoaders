<?php namespace Nine\Loaders\Sets;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Auryn\Injector;
use Nine\Loaders\ConfigurationSet;

/**
 * This is a sample ConfigurationSet descendant that supports
 * auto-configuration of an Auryn dependency injection container.
 */
class AurynConfigurationSet extends ConfigurationSet
{
    /** @var Injector */
    protected $container;

    /**
     * @param Injector|null $container
     *
     * @return AurynConfigurationSet
     */
    public function setContainer(Injector $container)
    {
        $this->container = $container;

        return $this;
    }

}
