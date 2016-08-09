<?php namespace Nine\Application\Containers;

/**
 * @package Nine Loader
 * @version 0.5.0
 */

use ArrayAccess;
use Interop\Container\ContainerInterface;
use Nine\Application\Containers\Contracts\ContainerCompatibilityInterface;
use Nine\Application\Containers\Exceptions\ContainerAbstractMakeException;
use Nine\Application\Containers\Exceptions\ContainerAbstractNotFoundException;
use Pimple\Container as PimpleContainer;
use Pimple\ServiceProviderInterface;

class ServiceContainer implements ContainerInterface, ContainerCompatibilityInterface, ArrayAccess
{
    /** @var PimpleContainer */
    private $pimpleContainer;

    /**
     * @param PimpleContainer|null $pimpleContainer
     */
    public function __construct(PimpleContainer $pimpleContainer = NULL)
    {
        $this->pimpleContainer = $pimpleContainer ?: new PimpleContainer();
    }

    public function __call($name, $arguments)
    {
        call_user_func_array([$this->pimpleContainer, $name], $arguments);
    }

    /**
     * @param ServiceProviderInterface $pimpleServiceProvider
     */
    public function addConfig(ServiceProviderInterface $pimpleServiceProvider)
    {
        $this->pimpleContainer->register($pimpleServiceProvider);
    }

    /**
     * A api compatibility method to support compiling the container.
     */
    public function compile()
    {
        # not applicable
    }

    /**
     *
     * @param string $serviceId
     *
     * @return mixed
     * @throws ContainerAbstractMakeException
     * @throws ContainerAbstractNotFoundException
     */
    public function get($serviceId)
    {
        if ( ! $this->has($serviceId)) {
            throw new ContainerAbstractNotFoundException('Cannot locate abstract (' . $serviceId . ') dependency.');
        }

        try {
            return $this->pimpleContainer[$serviceId];
        } catch (\Exception $exception) {
            throw new ContainerAbstractMakeException('Pimple container exception occurred', 0, $exception);
        }
    }

    /**
     * @param string $serviceId
     *
     * @return bool
     */
    public function has($serviceId)
    {
        return isset($this->pimpleContainer[$serviceId]);
    }

    /**
     * A api compatibility method to determine if the container has been
     * compiled or is otherwise locked.
     *
     * @return bool
     */
    public function isFrozen()
    {
        return FALSE;
    }

    public function offsetExists($serviceId)
    {
        return $this->has($serviceId);
    }

    public function offsetGet($serviceId)
    {
        $this->get($serviceId);
    }

    public function offsetSet($serviceId, $value)
    {
        $this->pimpleContainer->offsetSet($serviceId, $value);
    }

    public function offsetUnset($serviceId)
    {
        $this->pimpleContainer->offsetUnset($serviceId);
    }

    /**
     * @param ServiceProviderInterface $serviceProvider
     *
     * @return ServiceContainer
     */
    public static function constructConfiguredWith(ServiceProviderInterface $serviceProvider)
    {
        $container = new static();
        $container->addConfig($serviceProvider);

        return $container;
    }

    /**
     * An api compatibility method for defining parameters.
     *
     * @param $id
     * @param $value
     */
    public function setParameter($id, $value)
    {
        $this->pimpleContainer->offsetSet($id, $value);
    }
}
