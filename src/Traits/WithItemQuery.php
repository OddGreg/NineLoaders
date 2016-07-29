<?php namespace Nine\Loaders\Traits;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Nine\Library\Lib;

trait WithItemQuery
{
    /**
     * **Get a value from the collection by its dot-notated index.**
     *
     * @param string $query
     * @param null   $default
     *
     * @return mixed
     */
    public function get($query, $default = null)
    {
        return Lib::array_query($this->items, $query, value($default));
    }

    /**
     * **TRUE if an indexed value exists.**
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function has($key) : bool
    {
        return array_key_exists($key, $this->items);
    }

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
    public function put($key, $value)
    {
        # attempt writing the value to the key
        if (is_string($key)) {
            list($key, $value) = Lib::expand_segments($key, $value);
            $this->searchAndReplace([$key => $value]);
        }
    }

    /**
     * **Locate a value by `$key`, return `$default` if not found.**
     *
     * @param string|array $key
     * @param mixed        $default
     *
     * @return mixed
     */
    public function searchAndReplace($key, $default = null)
    {
        return Lib::array_search_and_replace($this->items, $key, $default);
    }

}
