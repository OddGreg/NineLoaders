<?php namespace Nine\Loaders\Traits;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Nine\Loaders\Exceptions\UnsupportedUseOfArrayAccessMethod;

trait WithConfigurationSetArray
{
    /**
     * @param mixed $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * @param mixed $key
     *
     * @return array|mixed
     * @throws \Nine\Loaders\Exceptions\ConfiguratorNotFoundException
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @throws UnsupportedUseOfArrayAccessMethod
     */
    public function offsetSet($key, $value)
    {
        throw new UnsupportedUseOfArrayAccessMethod("Direct array assignment is disabled. Please use add() instead. [$key|$value]");
    }

    public function offsetUnset($key)
    {
        if ($this->has($key)) {
            unset($this->configurators[$key]);
        }
    }

}
