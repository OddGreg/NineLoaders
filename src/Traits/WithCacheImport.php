<?php namespace Nine\Loaders\Traits;

use Nine\Library\Lib;
use Nine\Loaders\Exceptions\ConfigurationFileNotFound;
use Nine\Loaders\Exceptions\InvalidConfigurationImportValueException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

trait WithCacheImport
{
    /**
     * Register a configuration using the base name of the file.
     *
     * @param        $extension
     * @param        $filePath
     * @param string $key
     *
     * @return mixed
     * @throws ParseException
     * @throws \InvalidArgumentException
     * @throws InvalidConfigurationImportValueException
     */
    protected function importByExtension($extension, $filePath, $key = '')
    {
        $extension = strtolower(str_replace('*', '', $extension));
        $filePath = file_exists($filePath) ? $filePath : $this->basePath . "/$filePath";

        # Include only if the root key does not exist,
        # meaning that the file has already been imported.
        # Or so we assume.
        if ( ! array_key_exists($key, $this->cache)) {
            $import = false;

            if ($extension === '.php') {
                /** @noinspection UntrustedInclusionInspection */
                $import = include "$filePath";
            }

            if (in_array($extension, ['.yaml', '.yml'], true)) {
                $import = $this->importYAML($filePath);
            }

            if ($extension === '.json') {
                $import = $this->importJSON($filePath);
            }

            # only import if the config file returns an array
            if (is_array($import)) {
                $this->cache[$key] = $import;

                return;
            }

            throw new InvalidConfigurationImportValueException(
                "Configuration file `$filePath` did not return an array. (" . gettype($import) . ' given.)');
        }
    }

    /**
     * @param string $key
     * @param string $extension
     *
     * @return void
     * @throws InvalidConfigurationImportValueException
     * @throws ParseException
     * @throws \InvalidArgumentException
     * @throws ConfigurationFileNotFound
     */
    protected function importByPathOrKey(string $key, $extension = '.php')
    {
        // the first segment of a compound key is the filename
        $segments = explode('.', $key);

        // the cache key is the first segment
        $cacheKey = array_shift($segments);
        $filename = $cacheKey . $extension;

        $filePath = Lib::file_in_path($filename, [$this->basePath, './']);

        if (false === $filePath) {
            throw new ConfigurationFileNotFound("Could not locate either $filename or " . $this->basePath . $filename);
        }

        $this->importByExtension($extension, $filePath, $cacheKey);
    }

    /**
     * Import (merge) values from a json file or value into the collection by key.
     *
     * @param $jsonString
     *
     * @param $key
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function importJSON($jsonString, $key = '')
    {
        $jsonString = $this->getFileOrValue($jsonString);
        $import = json_decode($jsonString, true);

        // an empty key signifies direct import
        return $this->cacheKeyValue($import, $key);
    }

    /**
     * Import (merge) values from a yaml file.
     *
     * @param string $yamlString
     * @param string $key
     *
     * @return array
     * @throws \InvalidArgumentException
     *
     * @throws ParseException
     */
    protected function importYAML($yamlString, $key = '')
    {
        $yamlString = $this->getFileOrValue($yamlString);
        $import = (new Yaml())->parse($yamlString);

        return $this->cacheKeyValue($import, $key);
    }

    /**
     * Returns either the given value or, if the value is a valid
     * path to a file, the contents of a file.
     *
     * NOTE:
     *  The contents are simply read and not translated or interpreted.
     *  Do not use this method as a form of `include` for .php files.
     *
     * Example:
     *      // return 'Just a line of text.'
     *      `$reader->getFileOrValue('Just a line of text.')`
     *      // return the contents of the markdown file.
     *      `$reader->getFileOrValue(__DIR__ . '/config/templates.json')`
     *
     * @see importJSON, importYAML
     *
     * @param $value
     *
     * @return string
     */
    protected function getFileOrValue($value)
    {
        return file_exists($value) ? file_get_contents($value) : $value;
    }

    /**
     * Stash a value in the cache.
     *
     * If no key is given then replace the entire cache with
     * the value. If a key is provided then merge the value
     * on the key with the cache.
     *
     * @param $value
     * @param $key
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function cacheKeyValue($value, $key = null)
    {
        // an empty key signifies direct import
        return null !== $key
            ? $this->cache = array_merge($this->cache, $value)
            : $this->cache[$key] = $value;
    }
}
