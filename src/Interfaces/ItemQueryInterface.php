<?php namespace Nine\Loaders\Interfaces;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

interface ItemQueryInterface
{
    /**
     * **Get a value from the collection by its dot-notated index.**
     *
     * @param string $query
     * @param null   $default
     *
     * @return mixed
     */
    public function get($query, $default = null);

    /**
     * **TRUE if an indexed value exists.**
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function has($key) : bool;

    /**
     * Put a key:value pair into $items using search and replace.
     *
     * This is not the same as set() which naively replaces the existing key:Value.
     *
     * @param $key
     * @param $value
     *
     * @return void
     */
    public function put($key, $value);
}
