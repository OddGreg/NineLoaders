<?php namespace Nine\Loaders\Builders;

use Nine\Loaders\Exceptions\BuildOutOfSequenceError;
use Nine\Loaders\Interfaces\BuilderInterface;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
abstract class Builder implements BuilderInterface
{
    /** @var BuilderInterface */
    private $builder;

    private $building = false;

    private $structureIdentity;

    private $targetIdentity;

    /**
     * Builder constructor.
     *
     * @param BuilderInterface $builder
     */
    public function __construct(BuilderInterface $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Begin building the structure.
     *
     * @param string $targetIdentity    The class, filename or token to build.
     * @param string $structureIdentity The identifier given to this structure.
     *
     * @return static
     *
     * @throws BuildOutOfSequenceError
     */
    public function begin(string $targetIdentity, string $structureIdentity)
    {
        if ($this->building) {
            throw new BuildOutOfSequenceError('Cannot call build() again until end().');
        }

        $this->building = true;

        $this->targetIdentity = $targetIdentity;
        $this->structureIdentity = $structureIdentity;

        return $this;
    }

    /**
     * End building the structure.
     *
     * If all goes well, this should result in the finalization
     * of a single
     *
     * @return array
     * @throws \Nine\Loaders\Exceptions\BuildOutOfSequenceError
     */
    public function end()
    {
        if ( ! $this->building) {
            throw new BuildOutOfSequenceError('Cannot call end() before begin().');
        }

        $this->building = false;

    }

    /**
     *
     *
     * @param array ...$parameters
     */
    abstract public function perform(...$parameters);
}
