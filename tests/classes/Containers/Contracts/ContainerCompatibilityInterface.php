<?php namespace Nine\Application\Containers\Contracts;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Interop\Container\ContainerInterface as InteropContainerInterface;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
interface ContainerCompatibilityInterface extends InteropContainerInterface
{
    /**
     * A api compatibility method to support compiling the container.
     *
     * Do nothing if the feature is not supported.
     *
     * Origin: Symfony Dependency Injector
     */
    public function compile();

    /**
     * A api compatibility method to determine if the container has been
     * compiled or is otherwise locked.
     *
     * Return FALSE if the feature is not supported.
     *
     * Origin: Symfony Dependency Injector
     *
     * @return bool
     */
    public function isFrozen();

    /**
     * An api compatibility method for defining parameters.
     *
     * Origin: Symfony Dependency Injector
     *
     * @param $id
     * @param $value
     */
    public function setParameter($id, $value);

}
