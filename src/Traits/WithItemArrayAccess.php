<?php namespace Nine\Loaders\Traits;

/**
 * A Trait that completes a class that implements \ArrayAccess by
 * adding required and additional methods for accessing the underlying
 * container.
 *
 * @note    : The containing property must be `$items`.
 *
 * @package Nine Traits
 * @version 0.4.2
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Nine\Library\Lib;

/**
 * WithItemArrayAccess expects that an $items property exists. It cannot operate without it.
 *
 * @property array $items Reference to $items property for hinting.
 */
trait WithItemArrayAccess
{
    protected $items = [];

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        return $this->has($offset);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->put($offset, $value);
    }

    /**
     * @param mixed|string $offset
     */
    public function offsetUnset($offset)
    {
        Lib::array_forget($this->items, $offset);
    }
}
