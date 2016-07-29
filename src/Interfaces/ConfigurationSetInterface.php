<?php namespace Nine\Loaders\Interfaces;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

interface ConfigurationSetInterface extends RepositoryInterface
{
    /**
     * Steps through the Configurators in a ConfigurationSet and calls
     * each configure method in priority order.
     *
     * @return void
     */
    public function configure();

}
