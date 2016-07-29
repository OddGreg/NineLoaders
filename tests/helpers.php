<?php namespace Nine;

/**
 * Globally accessible convenience functions.
 *
 * @package Nine Loaders
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Closure;

if (PHP_VERSION_ID < 70000)
{
    echo('Formula 9 requires PHP versions >= 7.0.0');
    exit(1);
}

// if this helpers file is included more than once, then calculate
// the global functions exposed and return a simple catalog.

if (defined('HELPERS_LOADED'))
{
    return TRUE;
}

define('HELPERS_LOADED', TRUE);

if ( ! function_exists('array_accept'))
{
    /**
     * Get all of the given array except for a specified array of items.
     *
     * @param  array|string $keys
     * @param  array        $array
     *
     * @return array
     */
    function array_except($array, $keys)
    {
        return array_diff_key($array, array_flip((array) $keys));
    }
}

if ( ! function_exists('database_path'))
{

    /**
     * Returns the current database path.
     *
     * Note that this function mirrors the function of the same name
     * in the standalone Artisan application - giving Artisan the
     * ability to refer to the same directory is required.
     *
     * @param string|null $path
     *
     * @return string
     */
    function database_path($path = NULL)
    {
        return $path ? DATABASE . $path : DATABASE;
    }
}

if ( ! function_exists('is_not'))
{

    function is_not($subject)
    {
        return ! $subject;
    }
}

if ( ! function_exists('value'))
{
    /**
     *  Returns value of a variable. Resolves closures.
     *
     * @param  mixed $value
     *
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if ( ! function_exists('throw_now'))
{

    /**
     * @param $exception
     * @param $message
     *
     * @return null
     */
    function throw_now($exception, $message)
    {
        throw new $exception($message);
    }
}

if ( ! function_exists('throw_if'))
{
    /**
     * @param string  $exception
     * @param string  $message
     * @param boolean $if
     */
    function throw_if($if, $exception, $message)
    {
        if ($if)
        {
            throw new $exception($message);
        }
    }
}

if ( ! function_exists('throw_if_not'))
{
    /**
     * @param string  $exception
     * @param string  $message
     * @param boolean $if
     */
    function throw_if_not($if, $exception, $message)
    {
        if ( ! $if)
        {
            throw new $exception($message);
        }
    }
}

if ( ! function_exists('tail'))
{
    // blatantly stolen from Ionuț G. Stan on stack overflow
    function tail($filename)
    {
        $line = '';

        $f = fopen(realpath($filename), 'r');
        $cursor = -1;

        fseek($f, $cursor, SEEK_END);
        $char = fgetc($f);

        /**
         * Trim trailing newline chars of the file
         */
        while ($char === "\n" || $char === "\r")
        {
            fseek($f, $cursor--, SEEK_END);
            $char = fgetc($f);
        }

        /**
         * Read until the start of file or first newline char
         */
        while ($char !== FALSE && $char !== "\n" && $char !== "\r")
        {
            /**
             * Prepend the new char
             */
            $line = $char . $line;
            fseek($f, $cursor--, SEEK_END);
            $char = fgetc($f);
        }

        return $line;
    }
}

if ( ! function_exists('dd'))
{

    /**
     * Override Illuminate dd()
     *
     * @param null $value
     * @param int  $depth
     */
    function dd($value = NULL, $depth = 8)
    {
        ddump($value, $depth);
    }
}

if ( ! function_exists('words'))
{

    /**
     * Converts a string of space or tab delimited words as an array.
     * Multiple whitespace between words is converted to a single space.
     *
     * ie:
     *      words('one two three') -> ['one','two','three']
     *      words('one:two',':') -> ['one','two']
     *
     *
     * @param string $words
     * @param string $delimiter
     *
     * @return array
     */
    function words($words, $delimiter = ' ')
    {
        return explode($delimiter, preg_replace('/\s+/', ' ', $words));
    }
}

if ( ! function_exists('tuples'))
{

    /**
     * Converts an encoded string to an associative array.
     *
     * ie:
     *      tuples('one:1, two:2, three:3') -> ["one" => 1,"two" => 2,"three" => 3,]
     *
     * @param $tuples
     *
     * @return array
     */
    function tuples($tuples)
    {
        $array = words($tuples, ',');
        $result = [];

        foreach ($array as $tuple)
        {
            $ra = explode(':', $tuple);

            $key = trim($ra[0]);
            $value = trim($ra[1]);

            $result[$key] = is_numeric($value) ? (int) $value : $value;
        }

        return $result;
    }
}
