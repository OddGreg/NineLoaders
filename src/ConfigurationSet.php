<?php namespace Nine\Loaders;

use ArrayAccess;
use Nine\Loaders\Exceptions\ConfiguratorNotFoundException;
use Nine\Loaders\Exceptions\DuplicateConfiguratorException;
use Nine\Loaders\Exceptions\UnsupportedUseOfArrayAccessMethod;
use Nine\Loaders\Interfaces\ConfiguratorInterface;
use Nine\Loaders\Interfaces\Prioritizable;
use Nine\Loaders\Interfaces\RepositoryInterface;
use Nine\Loaders\Support\LoaderReflector;
use Nine\Loaders\Support\Priority;
use Nine\Loaders\Support\SymbolTable;
use Nine\Loaders\Traits\WithPrioritize;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
class ConfigurationSet implements ArrayAccess, Prioritizable, RepositoryInterface
{
    use WithPrioritize;

    /** @var array */
    protected $configurators = [];

    /** @var bool */
    protected $configured = false;

    /** @var string */
    protected $key;

    /** @var bool */
    protected $loaded = false;

    /** @var ConfigFileReader */
    protected $reader;

    /** @var SymbolTable $symbolTable */
    protected $symbolTable;

    /**
     * ConfigurationSet constructor.
     *
     * @param string           $key
     * @param ConfigFileReader $reader
     */
    public function __construct(string $key, ConfigFileReader $reader)
    {
        $this->reader = $reader;
        $this->key = $key;
        $this->priority = Priority::NORMAL;
        $this->symbolTable = new SymbolTable;
    }

    /**
     * Set the base symbol table with pre-defined symbols.
     *
     * This is used to provide a conduit between a ConfigurationSet
     * and a Configurator. Most often containers and configuration
     * objects are predefined instances.
     *
     * @param SymbolTable $symbols
     */
    public function setBaseSymbolTable(SymbolTable $symbols)
    {
        $this->symbolTable = $symbols;
    }

    /**
     * Steps through the Configurators in a ConfigurationSet and calls
     * each configure method in priority order.
     *
     * return $this
     */
    public function configure()
    {
        // priority order from HIGH to LOW
        foreach ($this->configurators as $key => &$entry) {

            if ( ! $entry['configured']) {
                /** @noinspection PhpUndefinedMethodInspection */
                $entry['configurator']->configure();
                $entry['configured'] = true;
                $entry['profile']['configured'] = microtime(true);
                $this->configured[] = $key;
            }

            unset($entry);
        }

        return $this;
    }

    /**
     * Get (or simply `get`) a configurator based on the key.
     *
     * @param string $key        The configurator ID.
     * @param array  $parameters Optional parameters to pass to the configurator;
     *
     * @return Configurator|ConfiguratorInterface
     * @throws ConfiguratorNotFoundException
     */
    public function get(string $key, array $parameters = []) : Configurator
    {
        if ( ! array_key_exists($key, $this->configurators)) {
            throw new ConfiguratorNotFoundException("A Configurator (id: $key) was not found.");
        }

        return $this->configurators[$key]['configurator'];
    }

    /**
     * Returns a copy of the internal configurators array.
     *
     * @return array
     */
    public function getConfigurators(): array
    {
        return array_merge($this->configurators, []);
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    public function has($key)
    {
        return isset($this->configurators[$key]);
    }

    /**
     * PrioritySet a configurator to a key.
     *
     * @param ConfiguratorInterface|Configurator $configurator
     *
     * @return $this
     *
     * @throws DuplicateConfiguratorException
     * @internal param string $key
     * @internal param int $priority
     */
    public function insert(Configurator $configurator)
    {
        $key = $configurator->getKey();

        if (array_key_exists($key, $this->configurators)) {
            throw new DuplicateConfiguratorException("Configurators cannot be overwritten once set. (id: $key)");
        }

        $configured = $loaded = false;
        $profile = ['added' => microtime(true), 'loaded' => 0.0, 'configured' => 0.0];
        $this->configurators[$key] = compact('configurator', 'configured', 'loaded', 'profile');
        $this->sortConfiguratorsByPriority();

        return $this;
    }

    /**
     * @param array $configurators
     *
     * @return $this
     * @throws Exceptions\KeyDoesNotExistException
     * @throws Exceptions\CannotDetermineDependencyTypeException
     * @throws DuplicateConfiguratorException
     */
    public function load(array $configurators = [])
    {
        // install any Configurators passed to the load method.
        foreach ($configurators as $configurator) {
            /** @var Configurator $configurator */
            $this->insert($configurator);
        }

        // here we may need to inject dependencies as may be expected
        // in the signature of the configurator load method, which can be overloaded.

        // trigger all load methods in priority order from HIGH to LOW
        foreach ($this->configurators as $key => &$configurator) {

            /** @var Configurator $class */
            $class = $configurator['configurator'];

            if (method_exists($class, 'preload')) {
                $symbols = new SymbolTable([
                    ConfigFileReader::class => ['type' => 'object', 'value' => $this->reader],
                    ConfigurationSet::class => ['type' => 'object', 'value' => $this],
                    'name'                  => ['type' => 'string', 'value' => $key],
                    'dataset'               => ['type' => 'string', 'value' => $class->getDataset()],
                ]);

                $injector = new LoaderReflector($symbols->mergeWith($this->symbolTable));

                $injector->invokeClassMethod(get_class($class), 'preload');
            }

            if ( ! $configurator['loaded']) {

                $class->load($this->reader);

                $configurator['profile']['loaded'] = microtime(true);
                $configurator['loaded'] = true;

                $this->loaded[] = $key;
            }
        }
        unset($configurator);

        return $this;
    }

    /**
     * @param mixed $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * @param mixed $key
     *
     * @return array|mixed
     * @throws \Nine\Loaders\Exceptions\ConfiguratorNotFoundException
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @throws UnsupportedUseOfArrayAccessMethod
     */
    public function offsetSet($key, $value)
    {
        throw new UnsupportedUseOfArrayAccessMethod("Direct array assignment is disabled. Please use add() instead. [$key|$value]");
    }

    public function offsetUnset($key)
    {
        if ($this->has($key)) {
            unset($this->configurators[$key]);
        }
    }

    /**
     * Sort the configurators array by descending priority.
     */
    private function sortConfiguratorsByPriority()
    {
        uasort($this->configurators, function ($left, $right) {
            /** @noinspection PhpUndefinedMethodInspection */
            return $left['configurator']->getPriority() <=> $right['configurator']->getPriority();
        });
    }

}
