<?php namespace Nine\Loaders\Traits;

use Nine\Loaders\Exceptions\InvalidPriorityTokenException;
use Nine\Loaders\Support\Priority;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

trait WithPrioritize
{

    /** @var int */
    protected $priority;

    /**
     * Return the priority value for this object.
     *
     * @return int
     */
    public function getPriority() : int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     *
     * @throws InvalidPriorityTokenException
     */
    public function setPriority($priority)
    {
        $this->priority = Priority::resolve($priority);
    }

}
