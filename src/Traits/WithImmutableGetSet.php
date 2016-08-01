<?php namespace Nine\Loaders\Traits;

use Nine\Loaders\Exceptions\PropertyIsInaccessibleException;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

trait WithImmutableGetSet
{
    /**
     * Limit magic property reading.
     *
     * @param $name
     *
     * @throws PropertyIsInaccessibleException
     */
    public function __get($name)
    {
        throw new PropertyIsInaccessibleException("The property '$name' is not accessible.");
    }

    /**
     * Disable magic property writing.
     *
     * @param $name
     * @param $value
     *
     * @throws PropertyIsInaccessibleException
     */
    public function __set($name, $value)
    {
        throw new PropertyIsInaccessibleException("There are no writable properties available. (property: $name)");
    }

}
