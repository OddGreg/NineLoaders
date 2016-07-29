<?php namespace Nine\Loaders\Interfaces;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

interface BuilderInterface
{
    /**
     * Begin building the structure.
     *
     * @param string $targetIdentity    The class, filename or token to build.
     * @param string $structureIdentity The identifier given to this structure.
     *
     * @return static
     */
    public function begin(string $targetIdentity, string $structureIdentity);

    /**
     * End building the structure.
     *
     * If all goes well, this should result in the finalization
     * of a single
     *
     * @return array
     */
    public function end();
}
