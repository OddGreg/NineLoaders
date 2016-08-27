<?php namespace Nine\Loaders\Support;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Nine\Library\Lib;
use Nine\Loaders\Exceptions\CannotDetermineDependencyTypeException;
use Nine\Loaders\Exceptions\KeyDoesNotExistException;
use Nine\Loaders\Exceptions\MethodDoesNotExistException;
use ReflectionClass;
use ReflectionParameter;

final class LoaderReflector extends SymbolTable
{
    /**
     * Instantiate the reflector and import any symbols passed
     * in a SymbolTable.
     *
     * @param SymbolTable|NULL $symbols
     */
    public function __construct(SymbolTable $symbols = NULL)
    {
        parent::__construct($symbols ? $symbols->toArray() : []);
    }

    /**
     * The main value in this is for debugging and testing.
     *
     * @return SymbolTable
     */
    public function copySymbolTable(): SymbolTable
    {
        return new SymbolTable($this->items);
    }

    /**
     * Using reflection, extract and return an array containing
     * the reflection and argument list for a class or callable.
     *
     * @param      $class
     * @param null $method
     *
     * @return array
     *
     * @throws KeyDoesNotExistException
     * @throws CannotDetermineDependencyTypeException
     */
    public function extractDependencies($class, $method = NULL)
    {
        # create a reflection based on the format of `$class` and `$method`.
        $reflection = $this->getReflection($class, $method);

        # locate the method arguments
        $arguments = ! $reflection instanceof ReflectionClass ? $reflection->getParameters() : [];
        $arg_list = $this->gatherParameters($arguments);

        # return an array containing the reflection and the list of qualified argument instances
        return compact('reflection', 'arg_list');
    }

    /**
     * Determine and return the type of reflection necessary to examine
     * a class or callable.
     *
     * @param      $class
     * @param null $method
     *
     * @return null|\ReflectionFunction|\ReflectionMethod
     */
    public function getReflection($class, $method = NULL)
    {
        if (is_array($class)) {
            list($class, $method) = $class;
        }

        return $this->makeClassReflection($class, $method);
    }

    /**
     * Invoke a class::method with extracted dependencies through reflection.
     *
     * @param string $class
     * @param string $method
     *
     * @return mixed Return whatever the invoked class method returns.
     *
     * @throws KeyDoesNotExistException
     * @throws CannotDetermineDependencyTypeException
     */
    public function invokeClassMethod(string $class, $method = NULL)
    {
        // is the class described by `class@method`?
        if (is_string($class) && Lib::str_has(':', $class)) {
            list($class, $method) = explode(':', $class);
        }

        // extract the route class dependencies
        $class_dependencies = $this->extractDependencies($class, $method);
        list($reflection, $arguments) = array_values($class_dependencies);

        return $this->instantiateClass($reflection, $class, $method, $arguments);

    }

    /**
     * Like invokeClassMethod but operates on an already instantiated object.
     *
     * @param      $object
     * @param null $method
     *
     * @return mixed
     */
    public function invokeObjectMethod($object, $method = NULL)
    {
        /** @var \ReflectionMethod $reflectionMethod */
        list($reflectionMethod, $arg_list) = array_values($this->extractDependencies($object, $method));

        return $reflectionMethod->invokeArgs($object, $arg_list);
    }

    /**
     * This internal method determines and returns the argument list
     * required by `Reflection::invokeArgs()` and is normally only
     * called by the `extractDependencies()` method.
     *
     * @param array $arguments
     *
     * @return array
     *
     * @throws CannotDetermineDependencyTypeException
     */
    protected function gatherParameters(array $arguments)
    {
        # build an argument list to pass to the closure/method
        # this will contain instantiated dependencies.
        # more or less
        $arg_list = [];

        foreach ($arguments as $key => $arg) {

            # determine and retrieve the class of the argument, if it exists.

            /** @var ReflectionParameter $arg */
            $dependency_class = ($arg->getClass() === NULL) ? NULL : $arg->getClass()->name;

            # use the stored value form the SymbolTable
            if ($this->has($dependency_class)) {
                $arg_list[] = $this->get($dependency_class)['value'];

                continue;
            }

            # use the default value if it exists
            if ($arg->isDefaultValueAvailable() && ! $this->has($arg->name)) {
                $arg_list[] = $arg->getDefaultValue();

                continue;
            }

            # handle newly-encountered, existing class
            if (class_exists($dependency_class)) {
                # a class exists as an argument but was not found in the Forge,
                # so instantiate the class with dependencies
                $arg_list[] = $concrete = $this->invokeClassMethod($dependency_class, '__construct'); # new $dependency_class;

                # store the new class object in the SymbolTable
                $this->setSymbol($dependency_class, $dependency_class, $concrete);

                continue;
            }

            # handle cases where a type is supplied for the variable.
            if ($arg->hasType()) {

                $name = $arg->name;

                if ($this->has($name) && (string)$arg->getType() === $this->getSymbolType($name)) {
                    $arg_list[] = $this->getSymbolValue($name);
                }

                continue;
            }
        }

        return $arg_list;
    }

    /**
     * @param $class
     * @param $method
     *
     * @return null|\ReflectionFunction|\ReflectionMethod
     */
    protected function makeClassReflection($class, $method)
    {
        # if a callable is supplied then treat it as a function
        if ($class instanceof \Closure) {
            return new \ReflectionFunction($class);
        }

        if (is_string($class) && Lib::str_has(':', $class)) {
            list($class, $method) = explode(':', $class);
        }

        $reflectionClass = new \ReflectionClass($class);

        return $reflectionClass->hasMethod($method)
            ? new \ReflectionMethod($class, $method)
            : $reflectionClass;
    }

    /**
     * Instantiate a class based on its reflection, name, method and arguments.
     *
     * This is normally only called by `invokeClassMethod()`.
     *
     * @param        $reflection
     * @param string $class
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     * @throws MethodDoesNotExistException
     */
    private function instantiateClass($reflection, string $class, string $method, array $arguments)
    {
        // construct a new class object.
        // this will trigger an early return
        if ($method === '__construct') {

            /** @var ReflectionClass $reflection */
            $reflection = new \ReflectionClass($class);

            // this is a simple call to instantiate a class.
            return $reflection->newInstanceArgs($arguments);
        }

        ///** @var \ReflectionMethod $reflection */
        $rf = new \ReflectionClass($class);

        if ( ! $rf->hasMethod($method)) {
            throw new MethodDoesNotExistException($reflection->name . '::' . $method . '() does not exist.');
        }

        // if the class has no constructor then use this to
        // instantiate it in the usual, non-dependency injecting way.
        $subjectClass = $class;

        // determine if a constructor exists and has required parameters
        if (is_string($class) && $rf->getConstructor()) {

            // extract its dependencies
            $dependencies = $this->extractDependencies($class, '__construct');

            // instantiate the object through its constructor
            $subjectClass = $rf->newInstanceArgs($dependencies['arg_list']);

        }

        /** @var \ReflectionMethod $reflection */
        return $reflection->invokeArgs(
            is_string($subjectClass) ? new $subjectClass : $subjectClass,
            $arguments);
    }
}
