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
        $arguments = ! $reflection instanceof ReflectionClass ? $arguments = $reflection->getParameters() : [];
        $arg_list = $this->gatherParameters($class, $method, $arguments);

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
        $reflection = NULL;

        if (NULL === $method) {
            // if no method supplied and $class is an array then assume:
            //      `[class ,method]`
            //
            # if a callable is supplied then treat it as a function
            if ($class instanceof \Closure) {
                return new \ReflectionFunction($class);
            }

            if (is_string($class) && Lib::str_has(':', $class)) {
                list($class, $method) = explode(':', $class);

                return new \ReflectionMethod($class, $method);
            }

            # if a callable is supplied then treat it as a function
            if ( ! is_array($class) && is_callable($class)) {
                return new \ReflectionFunction($class);
            }
        }

        if (is_array($class)) {
            // extract the class and method
            list($controller, $method) = $class;

            return new \ReflectionMethod($controller, $method);
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
        if (is_string($class) and Lib::str_has(':', $class)) {
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
     *
     * @param       $class
     * @param       $method
     * @param array $arguments
     *
     * @return array
     * @throws CannotDetermineDependencyTypeException
     */
    protected function gatherParameters($class, $method, array $arguments)
    {
        # build an argument list to pass to the closure/method
        # this will contain instantiated dependencies.
        # more or less
        $arg_list = [];

        foreach ($arguments as $key => $arg) {
            # determine and retrieve the class of the argument, if it exists.
            /** @var ReflectionParameter $arg */
            $dependency_class = ($arg->getClass() === NULL) ? NULL : $arg->getClass()->name;

            if ($this->has($dependency_class)) {
                $arg_list[] = $this->get($dependency_class)['value'];

                continue;
            }

            # use the default value if it exists
            if ($arg->isDefaultValueAvailable() && ! $this->has($arg->name)) {
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

                if ($this->has($name) and $type === $this->getSymbolType($name)) {
                    $arg_list[] = $this->getSymbolValue($name);
                }

                continue;
            }

            throw new CannotDetermineDependencyTypeException(
                "Unable to determine the type of parameter '{$arg->name}' in '$class::$method'");
        }

        return $arg_list;
    }

    /**
     * Instantiate a class based on its reflection, name, method and arguments.
     *
     * This is normally only called by `invokeClassMethod()`.
     *
     * @param $reflection
     * @param $class
     * @param $method
     * @param $arguments
     *
     * @return mixed
     *
     * @throws KeyDoesNotExistException
     * @throws CannotDetermineDependencyTypeException
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

        if ( ! isset($reflection->class)) {
            throw new \BadMethodCallException("Class $class must implement the apply() method.");
        }

        // optionally, transfer control over to the class:method.
        /** @var \ReflectionMethod $reflection */
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
