<?php namespace Nine\Loaders\Support;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
use Nine\Loaders\Exceptions\SymbolTypeMismatchError;

/**
 * Test the Collection Class
 *
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class LoaderReflectorTest extends \PHPUnit_Framework_TestCase
{
    /** @var LoaderReflector $reflector */
    protected $reflector;

    public function get_name_function(string $name)
    {
        return $name;
    }

    public function get_test_function()
    {
        return function () {
            return 100;
        };
    }

    public function setUp()
    {
        $this->reflector = new LoaderReflector(new SymbolTable([
            'name'     => SymbolTable::makeSymbol('string', 'Harry Henderson'),
            'age'      => SymbolTable::makeSymbol('double', 26.5),
            'score'    => SymbolTable::makeSymbol('integer', 95),
            'passed'   => SymbolTable::makeSymbol('boolean', TRUE),
            'courses'  => SymbolTable::makeSymbol('array', ['Introduction to Plastic', 'Plastics 101']),
            'class'    => SymbolTable::makeSymbol(SymbolTable::class, new SymbolTable()),
            'callable' => SymbolTable::makeSymbol(\Closure::class, function () { return 'this is a callable'; }),
        ]));
    }

    public function testDependencyInjection()
    {
        static::assertEquals('Harry Henderson', $this->reflector->invokeObjectMethod($this, 'get_name_function'));
    }

    public function testInstances()
    {
        static::assertSame('Harry Henderson', $this->reflector->getSymbolValue('name'));
        static::assertSame(gettype('Harry Henderson'), $this->reflector->getSymbolType('name'));

        static::assertSame(95, $this->reflector->getSymbolValue('score'));
        static::assertSame(gettype(95), $this->reflector->getSymbolType('score'));

        static::assertTrue($this->reflector->getSymbolValue('passed'));
        static::assertSame(gettype(TRUE), $this->reflector->getSymbolType('passed'));
        static::assertSame(gettype(FALSE), $this->reflector->getSymbolType('passed'));

        static::assertSame(['Introduction to Plastic', 'Plastics 101'], $this->reflector->getSymbolValue('courses'));
        static::assertSame(gettype(['Introduction to Plastic', 'Plastics 101']), $this->reflector->getSymbolType('courses'));

        static::assertSame(26.5, $this->reflector->getSymbolValue('age'));
        static::assertSame(gettype(26.5), $this->reflector->getSymbolType('age'));

        static::assertEquals(SymbolTable::class, get_class($this->reflector->getSymbolValue('class')));
        static::assertEquals(SymbolTable::class, $this->reflector->getSymbolType('class'));

        static::assertInstanceOf(\Closure::class, $this->reflector->getSymbolValue('callable'));
        static::assertEquals('this is a callable', $this->reflector->resolveSymbolValue('callable'));
        static::assertEquals(\Closure::class, $this->reflector->getSymbolType('callable'));

        $this->reflector->clear();
        static::assertEquals(0, $this->reflector->count());

        $this->reflector->setSymbolTable([
            'name'     => SymbolTable::makeSymbol('string', 'Harry Henderson'),
            'age'      => SymbolTable::makeSymbol('double', 26.5),
            'score'    => SymbolTable::makeSymbol('integer', 95),
            'passed'   => SymbolTable::makeSymbol('boolean', TRUE),
            'courses'  => SymbolTable::makeSymbol('array', ['Introduction to Plastic', 'Plastics 101']),
            'class'    => SymbolTable::makeSymbol(SymbolTable::class, new SymbolTable()),
            'callable' => SymbolTable::makeSymbol(\Closure::class, function () { return 'this is a callable'; }),
        ]);
        static::assertEquals(7, $this->reflector->count());
    }

    public function testReflectionTypes()
    {
        static::assertInstanceOf(\ReflectionFunction::class, $this->reflector->getReflection(function () { }));
        static::assertInstanceOf(\ReflectionMethod::class, $this->reflector->getReflection($this, 'get_test_function'));
        static::assertInstanceOf(\ReflectionMethod::class, $this->reflector->getReflection(static::class . ':get_test_function'));
        static::assertInstanceOf(\ReflectionMethod::class, $this->reflector->getReflection([$this, 'get_test_function']));
    }

    public function testSymbolTypeMismatchError()
    {
        // allocate a string variable called 'test'
        $this->reflector->setSymbol('test', 'string', '');

        // assigning a new double to 'age' should succeed.
        $this->reflector['age'] = 30.1;

        // this should throw a type mismatch error when
        // attempting to assign an double to a string.
        $this->expectException(SymbolTypeMismatchError::class);
        $this->reflector['test'] = 23.6;
    }

}
