<?php declare(strict_types = 1);

namespace Nine\Loaders\Interfaces;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Nine\Loaders\ConfigFileReader;

/**
 * @property ConfigFileReader $files
 */
interface Prioritizable
{
    /**
     * Return the priority value for this object.
     *
     * @return int
     */
    public function getPriority() : int;

    /**
     * Set the priority token for this object.
     *
     * @param int|string $priority
     */
    public function setPriority($priority);
}
