<?php namespace Nine\Loaders;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Nine\Library\Lib;
use Nine\Loaders\Interfaces\Prioritizable;
use Nine\Loaders\Support\LoaderReflector;
use Nine\Loaders\Support\Priority;
use Nine\Loaders\Support\SymbolTable;
use Nine\Loaders\Traits\WithLoaderSetArray;
use Nine\Loaders\Traits\WithPrioritize;

class LoaderSet implements Prioritizable, \ArrayAccess
{
    use WithPrioritize;
    use WithLoaderSetArray;

    protected $container;

    /** @var LoaderReflector */
    protected $reflector;

    /** @var SymbolTable $symbolTable */
    protected $symbolTable;

    /** @var string */
    private $key;

    /** @var array */
    private $sets = [];

    /**
     * LoaderSet constructor.
     *
     * @param string          $name              The identifier given to this loader set.
     * @param LoaderReflector $reflector
     * @param null            $container
     * @param array           $configurationSets An array of instantiated configuration sets.
     *
     * @throws Exceptions\InvalidSymbolTableDefinitionException
     * @throws Exceptions\SymbolTypeDefinitionTypeError
     */
    public function __construct(string $name, LoaderReflector $reflector, $container = NULL, array $configurationSets = [])
    {
        $this->key = $name;
        $this->priority = Priority::NORMAL;
        $this->reflector = $reflector;
        $this->symbolTable = new SymbolTable;
        $this->container = $container;

        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $this->importConfigurationSets((array)$configurationSets);
    }

    /**
     * Trigger ConfigurationSet `configure` methods in priority order.
     */
    public function configure()
    {
        foreach ($this->sets as $key => $set) {
            if ( ! $this->sets[$key]['configured']) {
                /** @var ConfigurationSet $set */
                $set['set']->configure();
                $this->sets[$key]['configured'] = TRUE;
            }
        }
    }

    /**
     * Locate a given configurator in a given configuration set.
     *
     * @param string      $setKey
     * @param string|NULL $searchKey
     * @param null        $default
     *
     * @return mixed|null
     */
    public function find(string $setKey, string $searchKey = NULL, $default = NULL)
    {
        if ($this->offsetExists($setKey)) {
            $set = $this->offsetGet($setKey);

            if (Lib::array_has($set['set'], $searchKey)) {
                return Lib::array_get($set['set'], $searchKey);
            }

            return $set;
        }

        return $default;
    }

    /**
     * Import one or more configuration sets.
     *
     * @param array $configurations
     *
     * @throws Exceptions\DuplicateConfiguratorException
     * @throws Exceptions\DuplicateConfigurationSetException
     * @throws Exceptions\InvalidPriorityTokenException
     * @throws Exceptions\RequiredParametersAreMissingException
     * @throws Exceptions\CannotDetermineDependencyTypeException
     * @throws Exceptions\InvalidSymbolTableDefinitionException
     * @throws Exceptions\KeyDoesNotExistException
     * @throws Exceptions\SymbolTableKeyNotFoundException
     * @throws Exceptions\SymbolTypeDefinitionTypeError
     */
    public function import(array $configurations = [])
    {
        $this->importConfigurationSets($configurations);
    }

    /**
     * Triggers all ConfigurationSet load methods, which cascade
     * through all Configurator load methods - in priority order.
     *
     * @throws Exceptions\DuplicateConfiguratorException
     */
    public function loadAll()
    {
        foreach ($this->sets as $key => $config) {

            if ( ! $this->sets[$key]['loaded']) {

                /** @noinspection PhpUndefinedMethodInspection */
                $config['set']->load();

                $this->sets[$key]['loaded'] = TRUE;
            }
        }

        return $this;
    }

    /**
     * @param SymbolTable $symbols
     */
    public function setBaseSymbolTable(SymbolTable $symbols)
    {
        $this->symbolTable = $symbols;
    }

    /**
     * @param $setKey
     * @param $set
     */
    protected function addConfigurationSet($setKey, $set)
    {
        $configured = $loaded = FALSE;
        $this->sets[$setKey] = compact('set', 'configured', 'loaded');

        $this->sortConfigurationSetsByPriority();
    }

    /**
     * The definition for a ConfigurationSet in the input array is as follows:
     *
     * [
     *      AurynConfigurationSet::class => [
     *          // the identifier given to this configuration set.
     *          'name' => 'app.di',
     *          // the path to the folder that contains configuration files
     *          // for this set.
     *          'config_path' => CONFIG,
     *          // the loader priority.
     *          'priority' => 'high'|'normal'|'low'|integer (1..1024),
     *          // the list of Configurators in this set.
     *          'config' => [
     *              // the configurator
     *              BladeConfigurator::class => [
     *                  // the identifier for this Configurator
     *                  'name' => 'blade',
     *                  // the data set loaded by the ConfigFileReader class
     *                  'dataset' => 'view.blade',
     *                  // the set priority.
     *                  'priority' => 'high'|'normal'|'low'|integer (1..1024),
     *                  // any settings to add or to override settings from the data set.
     *                  'config'   => [... any associative array or likeness]
     *          ],
     *          [...]
     *      ],
     *      [...]
     * ]
     *
     * @param array $configurationSets
     *
     * @throws Exceptions\DuplicateConfigurationSetException
     * @throws Exceptions\DuplicateConfiguratorException
     * @throws Exceptions\RequiredParametersAreMissingException
     * @throws Exceptions\InvalidPriorityTokenException
     * @throws Exceptions\CannotDetermineDependencyTypeException
     * @throws Exceptions\KeyDoesNotExistException
     * @throws Exceptions\SymbolTableKeyNotFoundException
     * @throws Exceptions\InvalidSymbolTableDefinitionException
     * @throws Exceptions\SymbolTypeDefinitionTypeError
     */
    protected function importConfigurationSets(array $configurationSets)
    {
        foreach ($configurationSets as $class => $config) {
            $set = $this->makeConfigurationSet($class, $config);
            $setKey = $set->getKey();

            if (array_key_exists($setKey, $this->sets)) {
                throw new Exceptions\DuplicateConfigurationSetException("The loader encountered a duplicate configuration set. (set: $setKey)");
            }

            $this->addConfigurationSet($setKey, $set);

            // collect the list of Configurators.
            foreach ($config['config'] as $configurator => $settings) {
                // add it to the ConfigurationSet.
                $set->insert($this->makeConfigurator($configurator, $settings));
            }
        }
    }

    /**
     * @param string $class
     * @param array  $config
     *
     * @return ConfigurationSet
     * @throws \Nine\Loaders\Exceptions\SymbolTableKeyNotFoundException
     *
     * @throws Exceptions\KeyDoesNotExistException
     * @throws Exceptions\CannotDetermineDependencyTypeException
     * @throws Exceptions\InvalidPriorityTokenException
     * @throws Exceptions\RequiredParametersAreMissingException
     */
    protected function makeConfigurationSet(string $class, array $config) : ConfigurationSet
    {
        try {
            $key = $config['name'];
            $path = $config['config_path'];
            $priority = $config['priority'];

        } catch (\Exception $e) {
            throw new Exceptions\RequiredParametersAreMissingException('One or more required parameters are missing. (ConfigurationSet)');
        }

        /** @var ConfigurationSet $set */
        // each configuration set uses its own instance of ConfigFileReader.

        if ( ! class_exists($class)) {
            $class = ConfigurationSet::class;
        }

        $set = new $class($key, new ConfigFileReader($path), $this->container);
        $set->setPriority(Priority::resolve($priority));
        $set->setBaseSymbolTable($this->symbolTable);

        return $set;
    }

    /**
     * @param string $configurator
     * @param array  $configuration
     *
     * @return Configurator
     *
     * @throws Exceptions\SymbolTypeDefinitionTypeError
     * @throws Exceptions\InvalidSymbolTableDefinitionException
     * @throws Exceptions\KeyDoesNotExistException
     * @throws Exceptions\SymbolTableKeyNotFoundException
     * @throws Exceptions\CannotDetermineDependencyTypeException
     * @throws Exceptions\InvalidPriorityTokenException
     */
    protected function makeConfigurator(string $configurator, array $configuration) : Configurator
    {
        $name = $configuration['name'];
        $dataset = $configuration['dataset'] ?? '';
        $priority = Priority::resolve($configuration['priority'] ?? Priority::NORMAL);
        $config = $configuration['config'] ?? [];

        return new $configurator($name, $dataset, $priority, $config);
    }

    /**
     * Sort the configuration sets cache by priority.
     */
    protected function sortConfigurationSetsByPriority()
    {
        uasort($this->sets,
            function ($left, $right) {
                /** @noinspection PhpUndefinedMethodInspection */
                return $left['set']->getPriority() <=> $right['set']->getPriority();
            }
        );
    }
}
