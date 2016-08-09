<?php namespace Nine\Loaders;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use ArrayAccess;
use Interop\Container\ContainerInterface;
use Nine\Loaders\Exceptions\ConfiguratorNotFoundException;
use Nine\Loaders\Exceptions\DuplicateConfiguratorException;
use Nine\Loaders\Interfaces\Prioritizable;
use Nine\Loaders\Interfaces\RepositoryInterface;
use Nine\Loaders\Support\LoaderReflector;
use Nine\Loaders\Support\Priority;
use Nine\Loaders\Support\SymbolTable;
use Nine\Loaders\Traits\WithConfigurationSetArray;
use Nine\Loaders\Traits\WithPrioritize;
use Symfony\Component\Yaml\Exception\ParseException;

class ConfigurationSet implements ArrayAccess, Prioritizable, RepositoryInterface
{
    use WithPrioritize;
    use WithConfigurationSetArray;

    /** @var array */
    protected $configurators = [];

    /** @var bool */
    protected $configured = FALSE;

    /** @var mixed $container */
    protected $container;

    /** @var string */
    protected $key;

    /** @var bool */
    protected $loaded = FALSE;

    /** @var ConfigFileReader */
    protected $reader;

    /** @var SymbolTable $symbolTable */
    protected $symbolTable;

    /**
     * ConfigurationSet constructor.
     *
     * Of Note: The `$container` is not type-hinted and may be null if the
     * current configuration set has no use for one. If a container instance
     * is given then its type will be determined where necessary. No type
     * validation is applied.
     *
     * @param string           $key
     * @param ConfigFileReader $reader
     * @param                  $container
     *
     * @throws Exceptions\SymbolTableKeyNotFoundException
     * @throws Exceptions\InvalidSymbolTableDefinitionException
     * @throws Exceptions\SymbolTypeDefinitionTypeError
     */
    public function __construct(string $key, ConfigFileReader $reader, $container = NULL)
    {
        $this->reader = $reader;
        $this->key = $key;
        $this->priority = Priority::NORMAL;
        $this->symbolTable = new SymbolTable;
        $this->container = $container;

        if ($container) {
            $this->symbolTable->setSymbol(get_class($container), get_class($container), $container);
        }
    }

    /**
     * Step through the Configurators in a ConfigurationSet and call
     * each configure method in priority order.
     *
     * return $this
     *
     * @throws Exceptions\CannotDetermineDependencyTypeException
     * @throws Exceptions\KeyDoesNotExistException
     * @throws Exceptions\SymbolTableKeyNotFoundException
     * @throws \InvalidArgumentException
     * @throws Exceptions\ConfigurationFileNotFound
     * @throws Exceptions\InvalidConfigurationImportValueException
     * @throws ParseException
     */
    public function configure()
    {
        // priority order from HIGH to LOW
        foreach ($this->configurators as $key => &$entry) {

            if ( ! $entry['configured']) {
                $this->applyConfigurator($key, $entry['configurator']);
                $entry['configured'] = TRUE;
                $entry['profile']['configured'] = microtime(TRUE);
                $this->configured[] = $key;
            }

            unset($entry);
        }

        return $this;
    }

    /**
     * Locate and return a configurator based on the key.
     *
     * @param string $key        The configurator ID.
     * @param array  $parameters Optional parameters to pass to the configurator;
     *
     * @return Configurator|\Nine\Loaders\Interfaces\ConfiguratorInterface
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
     * @return ConfigFileReader
     */
    public function getConfig(): ConfigFileReader
    {
        return $this->reader;
    }

    /**
     * Return a copy of the internal configurators array.
     *
     * @return array
     */
    public function getConfigurators(): array
    {
        return array_merge($this->configurators, []);
    }

    /**
     * Get the attached container instance.
     *
     * @return mixed
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get the key (name) of this ConfigurationSet.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Determine if a given configurator is registered.
     *
     * @param $key
     *
     * @return bool
     */
    public function has(string $key) : bool
    {
        return isset($this->configurators[$key]);
    }

    /**
     * PrioritySet a configurator to a key.
     *
     * @param \Nine\Loaders\Interfaces\ConfiguratorInterface|Configurator $configurator
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

        $configured = $loaded = FALSE;
        $profile = ['added' => microtime(TRUE), 'loaded' => 0.0, 'configured' => 0.0];
        $this->configurators[$key] = compact('configurator', 'configured', 'loaded', 'profile');
        $this->sortConfiguratorsByPriority();

        return $this;
    }

    /**
     * Create a configurator from a class name and an array of arguments
     *
     * @param string $class
     * @param array  $arg_list
     *
     * @return Configurator
     *
     * @throws Exceptions\InvalidPriorityTokenException
     * @throws DuplicateConfiguratorException
     */
    public function insertWithCreate(string $class, array $arg_list) : Configurator
    {
        $name = $arg_list['name'];
        $dataset = $arg_list['dataset'] ?? '';
        $priority = $arg_list['priority'] ?? 'normal';
        $config = $arg_list['config'] ?? [];

        $priority = Priority::resolve($priority);
        $set = new $class($name, $dataset, $priority, $config);

        $this->insert($set);

        return $set;
    }

    /**
     * @param array $configurators
     *
     * @return $this
     * @throws Exceptions\SymbolTableKeyNotFoundException
     * @throws Exceptions\KeyDoesNotExistException
     * @throws Exceptions\CannotDetermineDependencyTypeException
     * @throws DuplicateConfiguratorException
     */
    public function load(array $configurators = [])
    {
        // install any additional Configurators passed to the load method.
        foreach ($configurators as $configurator) {
            /** @var Configurator $configurator */
            $this->insert($configurator);
        }

        // trigger all load methods in priority order from HIGH to LOW
        foreach ($this->configurators as $key => &$configuratorRecord) {
            $configuratorRecord = $this->loadConfigurator($key, (array)$configuratorRecord);
        }

        // best practice
        unset($configuratorRecord);

        return $this;
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
     * Load the configurator referenced by the configurator record.
     * Handles profile information and general accounting.
     *
     * @param string $key
     * @param array  $configuratorRecord
     *
     * @return array
     */
    protected function loadConfigurator(string $key, array $configuratorRecord) : array
    {
        /** @var Configurator $class */
        $class = $configuratorRecord['configurator'];

        if ( ! $configuratorRecord['loaded']) {
            // the load method expects a reference to the current config reader
            $class->load($this->reader);

            // profiling and accounting
            $configuratorRecord['profile']['loaded'] = microtime(TRUE);
            $configuratorRecord['loaded'] = TRUE;

            // maintain a list of loaded configurators identified by key (name)
            $this->loaded[] = $key;
        }

        return $configuratorRecord;
    }

    /**
     * Using the built symbol table, invoke the `apply()` method on
     * a Configurator.
     *
     * @param string       $key
     * @param Configurator $class
     *
     * @throws Exceptions\SymbolTableKeyNotFoundException
     * @throws Exceptions\CannotDetermineDependencyTypeException
     * @throws Exceptions\KeyDoesNotExistException
     * @throws \InvalidArgumentException
     * @throws Exceptions\ConfigurationFileNotFound
     * @throws Exceptions\InvalidConfigurationImportValueException
     * @throws ParseException
     */
    private function applyConfigurator(string $key, Configurator $class)
    {
        if ( ! $this->configurators[$key]['configured']) {

            $injector = new LoaderReflector($this->buildSymbolTable($key, $class));
            $injector->invokeClassMethod(get_class($class), 'apply');

            // assuming that worked, handle accounting.
            $this->configurators[$key]['configured'] = TRUE;
            $this->configurators[$key]['profile']['loaded'] = microtime(TRUE);

            unset($injector);
        }
    }

    /**
     * Generate the `SymbolTable` for use when invoking Configurator::apply().
     *
     * We don't rely on any specific dependency injector. Instead, we use
     * `LoaderReflector` with this symbol table.
     *
     * Enable the reflector to type-hint special parameters by name or class.
     *
     * Legend:
     *      ConfigFileReader::class         The currently loaded ConfigFileReader
     *      ConfigurationSet::class         A reference to the containing configuration set
     *      array $config                   The configuration data for the configurator
     *      string $name                    The name of the configurator
     *      string $dataset                 The configurator's key into the ConfigFileReader
     *      $container::class $container    The configuration set container reference.
     *                                      i.e.: \Pimple\Container, \Auryn\Injector etc.
     *
     * @param string       $key
     * @param Configurator $class
     *
     * @return SymbolTable
     *
     * @see `applyConfigurator()` method.
     *
     * @throws ParseException
     * @throws Exceptions\InvalidConfigurationImportValueException
     * @throws Exceptions\ConfigurationFileNotFound
     * @throws \InvalidArgumentException
     * @throws Exceptions\SymbolTableKeyNotFoundException
     */
    private function buildSymbolTable(string $key, Configurator $class) : SymbolTable
    {
        // we don't know the class of the container, and it doesn't matter.
        // so we need to determine the class for the symbol table
        $containerClass = get_class($this->container);

        // classes
        if ($this->container && ! $this->symbolTable->has($containerClass)) {
            $this->symbolTable->setSymbol($containerClass, $containerClass, $this->container);
        }
        $this->symbolTable->setSymbol(ConfigFileReader::class, ConfigFileReader::class, $this->reader);
        $this->symbolTable->setSymbol(ConfigurationSet::class, ConfigurationSet::class, $this);

        /**
         * Parameters by name - MUST use the correct declaration.
         * ie: Configurator::apply(array $config, string $name, string $dataset)
         */

        $this->symbolTable->setSymbol('config', 'array', $this->reader->read($class->getDataset()));
        $this->symbolTable->setSymbol('name', 'string', $key);
        $this->symbolTable->setSymbol('dataset', 'string', $class->getDataset());
        $this->container instanceof ContainerInterface ? $this->symbolTable->setContainer($this->container) : NULL;

        return $this->symbolTable;
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
