<?php namespace Nine\Application\Containers;

use Auryn\Injector;
use Nine\Application\Containers\Contracts\ContainerCompatibilityInterface;
use Nine\Application\Containers\Exceptions\ContainerAbstractMakeException;
use Nine\Application\Containers\Exceptions\ContainerAbstractNotFoundException;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
class AurynDI extends Injector implements ContainerCompatibilityInterface
{
    /**
     * @var array
     */
    private $has = [];

    /**
     * A api compatibility method to support compiling the container.
     */
    public function compile()
    {
        # not applicable
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        if ( ! $this->has($id)) {
            throw new ContainerAbstractNotFoundException('Cannot locate abstract (' . $id . ') dependency.');
        }

        try {
            return $this->make($id);
        } catch (\Exception $previous) {
            throw new ContainerAbstractMakeException('The container failed attempting to make `' . $id . '`', 0, $previous);
        }
    }

    /**
     * @inheritdoc
     */
    public function has($id)
    {
        static $filter = Injector::I_BINDINGS
            | Injector::I_DELEGATES
            | Injector::I_PREPARES
            | Injector::I_ALIASES
            | Injector::I_SHARES;

        if (isset($this->has[$id])) {
            return $this->has[$id];
        }

        $definitions = array_filter($this->inspect($id, $filter));
        if (count($definitions) > 0) {
            return $this->has[$id] = TRUE;
        }

        if ( ! class_exists($id)) {
            return $this->has[$id] = FALSE;
        }

        $reflector = new \ReflectionClass($id);
        if ($reflector->isInstantiable()) {
            return $this->has[$id] = TRUE;
        }

        return $this->has[$id] = FALSE;
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

    public function setParameter($id, $value)
    {
        $this->defineParam($id, $value);
    }
}
