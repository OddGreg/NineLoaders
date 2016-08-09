<?php namespace Nine\Application\Containers\Traits;

use Auryn\Injector;
use Nine\Application\Containers\AurynDI;

/**
 * @package Nine
 * @version 0.4.2
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

trait WithInjectorArrayAccess
{
    /**
     * @var AurynDI
     */
    private $injector;

    /**
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @param string $offset
     *
     * @return Injector
     */
    public function offsetGet($offset)
    {
        return $this->injector->make($offset);
    }

    /**
     * @param string $offset
     * @param mixed  $value
     */
    public function offsetSet($offset, $value)
    {
        $this->injector->define($offset, (array) $value);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->injector[$offset]);
    }

}
