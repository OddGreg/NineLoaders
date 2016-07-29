<?php namespace Nine\Loaders\Support;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Nine\Library\Lib;
use Nine\Loaders\Exceptions\CannotDetermineDependencyTypeException;
use Nine\Loaders\Exceptions\KeyDoesNotExistException;
use ReflectionClass;
use ReflectionParameter;

final class LoaderReflector
{
    /** @var SymbolTable */
    protected $symbols;

    public function __construct(SymbolTable $symbols = null)
    {
        $this->symbols = $symbols ?: new SymbolTable();
        $this->lib = new Lib;
    }

    /**
     * @param      $class
     * @param null $method
     *
     * @return array
     * @throws KeyDoesNotExistException
     * @throws CannotDetermineDependencyTypeException
     */
    public function extractDependencies($class, $method = null)
    {
        # create a reflection based on the format of `$class` and `$method`.
        $reflection = $this->getReflection($class, $method);

        # locate the method arguments
        $arguments = ! $reflection instanceof ReflectionClass ? $arguments = $reflection->getParameters() : [];
        $arg_list = $this->gatherParameters($class, $method, $arguments);

        # return an array containing the reflection and the list of qualified argument instances
        return compact('reflection', 'arg_list');
    }

    /**
     * @param      $class
     * @param null $method
     *
     * @return null|\ReflectionFunction|\ReflectionMethod
     */
    public function getReflection($class, $method = null)
    {
        $reflection = null;

        if (null !== $method) {
            // if no method supplied and $class is an array then assume:
            //      `[class ,method]`
            //
            # if a callable is supplied then treat it as a function
            if ($class instanceof \Closure) {
                return new \ReflectionFunction($class);
            }

            if (is_array($class)) {
                // extract the class and method
                list($controller, $method) = $class;

                return new \ReflectionMethod($controller, $method);
            }

            # if a callable is supplied then treat it as a function
            if (is_callable($class)) {
                return new \ReflectionFunction($class);
            }
        }

        try {
            // try through a constructor
            $reflection = new \ReflectionMethod($class, $method);

        } catch (\ReflectionException $e) {

            // probably no constructor, so just instantiate the class.

            /** @var ReflectionClass $reflection */
            $reflection = new \ReflectionClass($class);
        }

        return $reflection;
    }

    /**
     * @return SymbolTable
     */
    public function getSymbols(): SymbolTable
    {
        return $this->symbols;
    }

    /**
     * @param      $class
     * @param null $method
     *
     * @return mixed
     * @throws KeyDoesNotExistException
     * @throws CannotDetermineDependencyTypeException
     */
    public function invokeClassMethod($class, $method = null)
    {
        // is the class described by `class@method`?
        if (is_string($class) and Lib::str_has(':', $class)) {
            list($class, $method) = explode(':', $class);
        }

        // extract the route class dependencies
        $class_dependencies = $this->extractDependencies($class, $method);
        list($reflection, $arguments) = array_values($class_dependencies);

        return $this->instantiateClass($reflection, $class, $method, $arguments);

    }

    /**
     * @param $class
     * @param $method
     * @param $arguments
     *
     * @return array
     * @throws KeyDoesNotExistException
     * @throws CannotDetermineDependencyTypeException
     */
    protected function gatherParameters($class, $method, $arguments)
    {
        # build an argument list to pass to the closure/method
        # this will contain instantiated dependencies.
        # more or less
        $arg_list = [];

        foreach ($arguments as $key => $arg) {
            # determine and retrieve the class of the argument, if it exists.
            /** @var ReflectionParameter $arg */
            $dependency_class = ($arg->getClass() === null) ? null : $arg->getClass()->name;

            if ($this->symbols->has($dependency_class)) {
                $arg_list[] = $this->symbols->get($dependency_class)['value'];

                continue;
            }

            # use the default value if it exists
            if ($arg->isDefaultValueAvailable() && ! $this->symbols->has($arg->name)) {
                $arg_list[] = $arg->getDefaultValue();

                continue;
            }

            # how about a valid class name?
            if (class_exists($dependency_class)) {
                # a class exists as an argument but was not found in the Forge,
                # so instantiate the class with dependencies
                $arg_list[] = $this->invokeClassMethod($dependency_class, '__construct'); # new $dependency_class;

                // next
                continue;
            }

            if ($arg->hasType()) {

                $type = (string)$arg->getType();
                $name = $arg->name;

                if ($this->symbols->has($name) and $type === $this->symbols->getSymbolType($name)) {
                    $arg_list[] = $this->symbols->getSymbolValue($name);
                }

                continue;
            }

            throw new CannotDetermineDependencyTypeException(
                "Unable to determine the type of parameter '{$arg->name}' in '$class::$method'");
        }

        return $arg_list;
    }

    /**
     * @param $reflection
     *
     * @param $class
     * @param $method
     * @param $arguments
     * @param $execute
     *
     * @return mixed
     * @throws \Nine\Loaders\Exceptions\KeyDoesNotExistException
     * @throws \Nine\Loaders\Exceptions\CannotDetermineDependencyTypeException
     */
    private function instantiateClass($reflection, $class, $method, $arguments)
    {
        // construct a new class object.
        // this will trigger an early return
        if ($method === '__construct') {

            /** @var ReflectionClass $reflection */
            $reflection = new \ReflectionClass($class);

            // this is a simple call to instantiate a class.
            return $reflection->newInstanceArgs($arguments);
        }

        // optionally, transfer control over to the class:method.
        /** @var \ReflectionClass $rf */
        $rf = new \ReflectionClass($reflection->class);

        $constructor = $class;

        // determine if a constructor exists and has required parameters
        if (is_string($class) && $rf->getConstructor()) {
            // extract its dependencies
            $dependencies = $this->extractDependencies($class, '__construct');
            // instantiate the object through its constructor
            $constructor = $rf->newInstanceArgs($dependencies['arg_list']);
        }

        // invoke the method
        /** @var \ReflectionMethod $reflection */
        return $reflection->invokeArgs($constructor, $arguments);
    }

}
