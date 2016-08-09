<?php namespace Nine\Application\Containers;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Nine\Application\Containers\Contracts\ContainerCompatibilityInterface;
use Nine\Application\Containers\Exceptions\ContainerAbstractNotFoundException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;

/**
 * The only purpose for this class is to register its compliance
 * with the Container Interop ContainerInterface
 */
class SymfonyDI extends ContainerBuilder implements ContainerCompatibilityInterface
{
    /**
     * @param string $id
     * @param int    $invalidBehavior
     *
     * @return mixed|object|void
     *
     * @throws ContainerAbstractNotFoundException
     */
    public function get($id, $invalidBehavior = SymfonyContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        # note that the symfony DI converts all ids to lowercase.
        if ( ! parent::has($id)) {
            throw new ContainerAbstractNotFoundException('Cannot locate abstract (' . $id . ') dependency.');
        }

        return parent::get($id, $invalidBehavior);
    }

}
