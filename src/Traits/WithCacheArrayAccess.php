<?php namespace Nine\Loaders\Traits;

use Nine\Library\Lib;
use Nine\Loaders\Exceptions\DuplicateConfigurationKeyException;
use Nine\Loaders\Exceptions\InvalidConfigurationImportValueException;
use Nine\Loaders\Exceptions\KeyDoesNotExistException;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
trait WithCacheArrayAccess
{
    /** @var array */
    protected $cache = [];

    /**
     * Determines whether a give key can be resolved from the cache.
     *
     * If not found then attempt to load the config file indicated by the key
     * or the first segment of a compound key.
     *
     * (Only .php files are supported for auto-discovery.)
     *
     * @param mixed $compoundKey
     *
     * @return bool
     */
    public function offsetExists($compoundKey)
    {
        // if the key does not exist then attempt reading
        // the corresponding php include file.
        if ( ! Lib::array_has($this->cache, $compoundKey)) {

            // if this is a compound key then segregate the first element as the key
            $search = explode('.', $compoundKey, 2)[0];

            // if an attempt fails at finding the key then return false
            try {
                $this->importByPathOrKey($search);
            } catch (\Exception $e) {
                return false;
            }
        }

        // try finding the key (again)
        return Lib::array_has($this->cache, $compoundKey);
    }

    /**
     * Returns the cache entry for a given key.
     *
     * If the key cannot be found then attempt loading the related
     * config file. (Only .php files are supported for auto-discovery.)
     *
     * @param mixed $compoundKey
     *
     * @return mixed
     * @throws KeyDoesNotExistException
     */
    public function offsetGet($compoundKey)
    {
        if ($this->offsetExists($compoundKey)) {
            return Lib::array_get($this->cache, $compoundKey);
        }

        throw new KeyDoesNotExistException("The key ('$compoundKey') is empty or missing.");
    }

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @throws DuplicateConfigurationKeyException
     * @throws InvalidConfigurationImportValueException
     */
    public function offsetSet($key, $value)
    {
        //if ( ! Lib::is_assoc($value)) {
        //    throw new InvalidConfigurationImportValueException(
        //        'Only associative arrays may be imported. (' . gettype($value) . ' given)');
        //}

        if (array_key_exists($key, $this->cache)) {
            throw new DuplicateConfigurationKeyException("Overwriting existing configuration keys is not allowed. (key: $key)");
        }

        $this->cache[$key] = $value;
    }

    public function offsetUnset($key)
    {
        Lib::array_forget($this->cache, $key);
    }

}
