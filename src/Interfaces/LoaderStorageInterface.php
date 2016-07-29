<?php namespace Nine\Loaders\Interfaces;

/**
 * @package Nine
 * @version 0.4.2
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

interface LoaderStorageInterface
{
    /**
     * Retrieve a unserialized Loader from the persistence layer.
     *
     * @param string $key
     *
     * @return LoaderInterface
     */
    public function retrieve(string $key) : LoaderInterface;

    /**
     * Stores a serialized Loader to the persistence layer.
     *
     * @param string          $key
     * @param LoaderInterface $loader
     *
     * @return void
     */
    public function store(string $key, LoaderInterface $loader);
}
