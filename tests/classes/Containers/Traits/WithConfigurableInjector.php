<?php namespace Nine\Application\Containers\Traits;

/**
 * @package Nine
 * @version 0.4.2
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

trait WithConfigurableInjector
{
    /**
     * @var array
     */
    private $definitions;

    /**
     * Parse a configuration array into ResearchContainer entries.
     *
     * structure: ['key' => ['share|define|define.param|add' => [$key=>$value|$value],]
     *
     * @param $array
     *
     * @return $this
     */
    public function load($array)
    {
        foreach ((array) $array as $item => $definition) {
            $this->importDeclaration($item, $definition);
        }

        return $this;
    }

    /**
     * @param $configurationType
     * @param $definition
     */
    private function importDeclaration($configurationType, $definition)
    {
        switch ($configurationType) {
            case 'add':
                $this->registerAdds($definition);
                break;

            case 'alias':
                $this->registerAliases($definition);
                break;

            case 'define':
                $this->registerDefines($definition);
                break;

            case 'define.param':
                $this->registerDefinedParameters($definition);
                break;

            case 'delegate':
                $this->registerDelegates($definition);
                break;

            case 'extend':
                $this->registerExtensions($definition);
                break;

            case 'share':
                $this->registerShares($definition);
                break;

            default :
                break;
        }
    }

    /**
     * @param $definitions
     */
    private function registerAdds($definitions)
    {
        foreach ((array) $definitions as $class => $define) {
            $this->add($class, $define);
        }
    }

    private function registerAliases($aliases)
    {
        foreach ((array) $aliases as $original => $alias) {
            $this->alias($original, $alias);
        }
    }

    private function registerDefinedParameters(array $parameters)
    {
        foreach ((array) $parameters as $paramName => $value) {
            $this->defineParam($paramName, $value);
        }
    }

    private function registerDefines(array $defines)
    {
        foreach ($defines as $key => $define) {
            $this->define($key, $define);
        }
    }

    private function registerDelegates($delegates)
    {
        foreach ((array) $delegates as $name => $callableOrMethodStr) {
            $this->delegate($name, $callableOrMethodStr);
        }
    }

    /**
     * @param $definitions
     */
    private function registerExtensions($definitions)
    {
        foreach ((array) $definitions as $name => $callableOrMethodStr) {
            $this->extend($name, $callableOrMethodStr);
        }
    }

    private function registerShares($classes)
    {
        foreach ($classes as $class) {
            $this->share($class);
        }
    }
}
