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
use Nine\Loaders\Traits\WithPrioritize;

class LoaderSet implements Prioritizable, \ArrayAccess
{
    Use WithPrioritize;

    /** @var LoaderReflector */
    protected $reflector;

    /** @var string */
    private $key;

    /** @var array */
    private $sets = [];

    /**
     * LoaderSet constructor.
     *
     * @param LoaderReflector $reflector
     * @param string          $name              The identifier given to this loader set.
     * @param array           $configurationSets An array of instantiated configuration sets.
     */
    public function __construct(LoaderReflector $reflector, string $name, array $configurationSets = [])
    {
        $this->key = $name;
        $this->priority = Priority::NORMAL;
        $this->reflector = $reflector;

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
                $this->sets[$key]['configured'] = true;
            }
        }
    }

    /**
     * @param string      $setKey
     * @param string|NULL $searchKey
     * @param null        $default
     *
     * @return mixed|null
     */
    public function find(string $setKey, string $searchKey = null, $default = null)
    {
        //ddump([compact('setKey','searchKey'), $this->sets, $this->offsetExists($setKey), $this->offsetGet($setKey)]);

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
                $config['set']->{'load'}();
                $this->sets[$key]['loaded'] = true;
            }
        }

        return $this;
    }

    public function offsetExists($key)
    {
        return array_key_exists($key, $this->sets);
    }

    public function offsetGet($key)
    {

        return $this->sets[$key];
    }

    public function offsetSet($key, $value)
    {
        throw new Exceptions\UnsupportedUseOfArrayAccessMethod(
            "To import set(s) to the loader, use 'import()'");
    }

    public function offsetUnset($key)
    {
        throw new Exceptions\UnsupportedUseOfArrayAccessMethod(
            'Sets cannot be removed once imported.');
    }

    /**
     * @param $setKey
     * @param $set
     */
    protected function addConfigurationSet($setKey, $set)
    {
        $configured = $loaded = false;
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
                throw new Exceptions\DuplicateConfigurationSetException("The loader encounter a duplicate configuration set. (set: $setKey)");
            }

            $this->addConfigurationSet($setKey, $set);

            $configurators = $config['config'];

            // collect the list of Configurators.
            foreach ($configurators as $configurator => $configuration) {
                // add it to the ConfigurationSet.
                $set->insert($this->makeConfigurator($configurator, $configuration));
            }
        }
    }

    /**
     * @param string $class
     * @param array  $config
     *
     * @return ConfigurationSet
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
        $set = new $class($key, new ConfigFileReader($path));
        $set->setPriority(Priority::resolve($priority));

        if (method_exists($set, 'setContainer')) {
            $dependencies = (object)$this->reflector->extractDependencies($class, 'setContainer');
            $dependencies->reflection->invokeArgs($set, $dependencies->arg_list);
        }

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
        // Configurator parameters.
        $name = $configuration['name'];
        $dataset = $configuration['dataset'] ?? '';
        $priority = Priority::resolve($configuration['priority'] ?? Priority::NORMAL);
        $config = $configuration['config'] ?? [];

        return new $configurator($name, $dataset, $priority, $config);

        // the Configurator constructor signature is:
        //   string $name, string $dataset = '', int $priority = Priority::NORMAL, array $config = []
        // so assign values to those parameters in the symbol table.
        //$this->reflector->getSymbols()->setSymbolTable([
        //    'name'     => ['type' => 'string', 'value' => $name],
        //    'dataset'  => ['type' => 'string', 'value' => $dataset],
        //    'priority' => ['type' => 'int', 'value' => $priority],
        //    'config'   => ['type' => 'array', 'value' => $config],
        //]);
        //
        //// construct the configurator
        //return $this->reflector->invokeClassMethod("$configurator:__construct");
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
