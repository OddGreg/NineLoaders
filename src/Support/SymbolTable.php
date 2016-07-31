<?php namespace Nine\Loaders\Support;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Nine\Library\Lib;
use Nine\Loaders\Exceptions\InvalidSymbolTableDefinitionException;
use Nine\Loaders\Exceptions\KeyDoesNotExistException;
use Nine\Loaders\Exceptions\SymbolTableKeyNotFoundException;
use Nine\Loaders\Exceptions\SymbolTypeDefinitionTypeError;
use Nine\Loaders\Exceptions\SymbolTypeMismatchError;
use Nine\Loaders\Interfaces\ItemQueryInterface;
use Nine\Loaders\Traits\WithItemArrayAccess;
use Nine\Loaders\Traits\WithItemQuery;

/**
 * A rudimentary symbol table for use with LoaderReflector
 */
final class SymbolTable implements \ArrayAccess, ItemQueryInterface
{
    use WithItemArrayAccess;
    use WithItemQuery;

    public function __construct(array $items = [])
    {
        $this->items = $this->checkedSymbolArray($items);
    }

    /**
     * @param $key
     *
     * @return array
     * @throws KeyDoesNotExistException
     */
    public function getSymbolType($key)
    {
        $this->keyExists($key);

        return $this->items[$key]['type'];
    }

    /**
     * @param $key
     *
     * @return array
     * @throws KeyDoesNotExistException
     */
    public function getSymbolValue($key)
    {
        $this->keyExists($key);

        return $this->items[$key]['value'];
    }

    /**
     * @param string $type
     * @param        $value
     *
     * @return array
     */
    public function makeSymbol(string $type, $value)
    {
        return compact('type', 'value');
    }

    /**
     * Merge the new symbols with the current symbols.
     * NOTE: Any symbols with the same key will be overwritten.
     *
     * @param SymbolTable $symbolTable
     *
     * @return $this
     */
    public function mergeWith(SymbolTable $symbolTable)
    {
        foreach ($symbolTable as $key => $symbol) {
            $this->items[$key] = $symbol;
        }

        return $this;
    }

    public function offsetGet($key)
    {
        $this->keyExists($key);

        return $this->items[$key]['value'];
    }

    /**
     * Override offsetSet to validate symbol structure
     *
     * @param $key
     * @param $symbol
     *
     * @throws InvalidSymbolTableDefinitionException
     * @throws KeyDoesNotExistException
     * @throws SymbolTypeDefinitionTypeError
     * @throws \Nine\Loaders\Exceptions\SymbolTableKeyNotFoundException
     */
    public function offsetSet($key, $symbol)
    {
        $this->setSymbolValue($key, $symbol);
    }

    /**
     * @param string $key
     * @param string $type
     * @param mixed  $value
     *
     * @throws SymbolTableKeyNotFoundException
     */
    public function setSymbol(string $key, string $type, $value)
    {
        $this->items[$key] = compact('type', 'value');
    }

    /**
     * @param array $symbols
     *
     * @throws InvalidSymbolTableDefinitionException
     * @throws SymbolTypeDefinitionTypeError
     */
    public function setSymbolTable(array $symbols)
    {
        $this->checkedSymbolArray($symbols);
        $this->items = $symbols;
    }

    /**
     * @param $key
     * @param $value
     *
     * @throws KeyDoesNotExistException
     */
    public function setSymbolValue($key, $value)
    {
        $this->keyExists($key);
        $this->keyTypeMatches($key, gettype($value));

        $this->items[$key]['value'] = $value;
    }

    /**
     * @param array $items
     *
     * @return array
     *
     * @throws InvalidSymbolTableDefinitionException
     * @throws SymbolTypeDefinitionTypeError
     */
    protected function checkedSymbolArray(array $items)
    {
        foreach ($items as $key => $item) {
            $this->checkSymbol($key, $item);
        }

        return $items;
    }

    /**
     * @param $key
     *
     * @return bool
     * @throws KeyDoesNotExistException
     */
    protected function keyExists($key)
    {
        if ( ! array_key_exists($key, $this->items)) {
            throw new KeyDoesNotExistException("Symbol '$key' does not exist.");
        }

        return true;
    }

    protected function keyTypeMatches(string $key, string $type)
    {
        $expected = $this->getSymbolType($key);

        if ($expected !== $type) {
            throw new SymbolTypeMismatchError(
                "Provided value for symbol '$key' does not match expected type '$expected'. Type '$type' given.");
        }
    }

    /**
     * @param string $key
     * @param        $symbol
     *
     * @throws InvalidSymbolTableDefinitionException
     * @throws SymbolTypeDefinitionTypeError
     */
    private function checkSymbol(string $key, $symbol)
    {
        if ( ! Lib::is_assoc($symbol) || ! isset($symbol['type']) || ! isset($symbol['value'])) {
            throw new InvalidSymbolTableDefinitionException(
                "Symbol information for '$key' must be an array in the form: ['type'=>type, 'value'=>value]");
        }

        if ('string' !== gettype($symbol['type'])) {
            throw new SymbolTypeDefinitionTypeError('The symbol "type" parameter must be a string');
        }
    }

}
