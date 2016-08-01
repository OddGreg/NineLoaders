<?php namespace Nine\Loaders\Traits;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Nine\Loaders\Exceptions\UnsupportedUseOfArrayAccessMethod;

trait WithLoaderSetArray
{
    /**
     * @param mixed $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->sets);
    }

    /**
     * @param mixed $key
     *
     * @return array|mixed
     */
    public function offsetGet($key)
    {
        return $this->sets[$key];
    }

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @throws UnsupportedUseOfArrayAccessMethod
     */
    public function offsetSet($key, $value)
    {
        throw new UnsupportedUseOfArrayAccessMethod(
            "To import set(s) to the loader, use 'import()'");
    }

    /**
     * @param mixed $key
     *
     * @throws UnsupportedUseOfArrayAccessMethod
     */
    public function offsetUnset($key)
    {
        throw new UnsupportedUseOfArrayAccessMethod(
            'Sets cannot be removed once imported.');
    }

}
