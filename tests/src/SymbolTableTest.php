<?php namespace Nine\Loaders;

use Nine\Application\Containers\AurynDI;
use Nine\Loaders\Exceptions\InvalidSymbolTableDefinitionException;
use Nine\Loaders\Exceptions\KeyDoesNotExistException;
use Nine\Loaders\Exceptions\SymbolTypeDefinitionTypeError;
use Nine\Loaders\Exceptions\SymbolTypeMismatchError;
use Nine\Loaders\Support\SymbolTable;
use SymbolForGet;

/**
 * Test the Collection Class
 *
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class SymbolTableTest extends \PHPUnit_Framework_TestCase
{
    /** @var SymbolTable */
    protected $st;

    public function setUp()
    {
        $container = new AurynDI();
        $container->define(SymbolForGet::class, [
            'initialized'
        ]);

        $this->st = new SymbolTable([
            'name'    => ['type' => 'string', 'value' => 'George'],
            'count'   => ['type' => 'int', 'value' => 10],
            'compass' => ['type' => 'array', 'value' => ['north', 'east', 'west', 'south',]],
        ]);

        $this->st->setContainer($container);
    }

    public function test_array_access()
    {
        static::assertEquals('George', $this->st['name']);
        $this->st['name'] = 'Patricia';
        static::assertEquals('Patricia', $this->st['name']);

        $this->expectException(SymbolTypeMismatchError::class);
        $this->st['name'] = 100;
    }

    public function test_fail_symbol_table_at_instantiation()
    {
        $this->expectException(SymbolTypeDefinitionTypeError::class);
        new SymbolTable(['test' => ['type' => 100, 'value' => 'value']]);
    }

    public function test_fail_table_definition_at_instantiation()
    {
        $this->expectException(InvalidSymbolTableDefinitionException::class);
        new SymbolTable(['test' => ['nope' => 100, 'value' => 'value']]);
    }

    public function test_key_does_not_exist()
    {
        $this->expectException(KeyDoesNotExistException::class);
        static::assertEquals('George', $this->st['not-name']);
    }

    public function test_make_symbol()
    {
        $symbol = $this->st->makeSymbol('string', 'happy');
        static::assertEquals(['type' => 'string', 'value' => 'happy'], $symbol);
    }

    public function test_symbol_get()
    {
        /** @var SymbolForGet $symbol */
        $symbol = $this->st->getSymbolValue(SymbolForGet::class);
        static::assertInstanceOf(SymbolForGet::class, $symbol);
        static::assertEquals('initialized', $symbol->getMessage());

        static::assertEquals('George', $this->st->getSymbolValue('name'));
        static::assertEquals('north', $this->st->getSymbolValue('compass')[0]);
        static::assertEquals('array', $this->st->getSymbolType('compass'));

        $this->st->setSymbol('time', gettype(microtime(TRUE)), microtime(TRUE));
        static::assertEquals('double', $this->st->getSymbolType('time'));

        $this->expectException(KeyDoesNotExistException::class);
        static::assertEquals('not there', $this->st->getSymbolType('non_symbol'));
    }

    public function test_symbol_set()
    {
        $this->st->setSymbol('city', 'string', 'Vancouver');
        static::assertEquals('Vancouver', $this->st->getSymbolValue('city'));
        $this->st->setSymbol('city', 'string', 'Seattle');
        static::assertEquals('Seattle', $this->st->getSymbolValue('city'));

        $this->expectException(SymbolTypeMismatchError::class);
        $this->st->setSymbolValue('city', 10.5);
    }

}
