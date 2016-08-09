<?php namespace Nine\Application\Containers;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Illuminate\Container\Container as IlluminateContainer;
use Nine\Application\Containers\Contracts\ContainerCompatibilityInterface;
use Nine\Application\Containers\Exceptions\ContainerAbstractMakeException;
use Nine\Application\Containers\Exceptions\ContainerAbstractNotFoundException;

class IlluminateDI extends IlluminateContainer implements ContainerCompatibilityInterface
{
    /**
     * A api compatibility method to support compiling the container.
     */
    public function compile()
    {
        # not applicable
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     * @param array  $parameters
     *
     * @return mixed No entry was found for this identifier.
     *
     * @throws ContainerAbstractMakeException
     * @throws ContainerAbstractNotFoundException
     */
    public function get($id, $parameters = [])
    {
        if ( ! $this->has($id)) {
            throw new ContainerAbstractNotFoundException('Cannot locate abstract (' . $id . ') dependency.');
        }

        try {
            return $this->make($id, $parameters);
        } catch (\Exception $previous) {
            throw new ContainerAbstractMakeException('The container failed attempting to make `' . $id . '`', 0, $previous);
        }
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id)
    {
        return $this->bound($id);
    }

    /**
     * A api compatibility method to determine if the container has been
     * compiled or is otherwise locked.
     *
     * @return bool
     */
    public function isFrozen()
    {
        return FALSE;
    }

    /**
     * An api compatibility method for defining parameters.
     *
     * @param $id
     * @param $value
     */
    public function setParameter($id, $value)
    {
        $this->instance($id, $value);
    }
}
