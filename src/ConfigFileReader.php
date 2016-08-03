<?php namespace Nine\Loaders;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Countable;
use InvalidArgumentException;
use Nine\Library\Lib;
use Nine\Loaders\Exceptions\ConfigurationFileNotFound;
use Nine\Loaders\Exceptions\InvalidConfigurationImportValueException;
use Nine\Loaders\Exceptions\InvalidConfigurationPathException;
use Nine\Loaders\Exceptions\UnsupportedUseOfArrayAccessMethod;
use Nine\Loaders\Traits\WithCacheArrayAccess;
use Nine\Loaders\Traits\WithCacheImport;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * ConfigFileReader caches configuration data indexed by key.
 *
 * Ultimately, the class reads and interprets configuration data
 * from text files. YAML, JSON and PHP configuration files are
 * supported. Only PHP files are interpreted during import.
 *
 * All import sources must return an array of `key:value` pairs
 * where `value` is either a scalar or an array.
 *
 * Note:
 *      This class provides easy access to configuration values
 *      via array access. Thus `$reader['view.blade.enabled']`
 *      would take the following steps:
 *
 *      1. Determine if the key 'view' is cached.
 *      2. If not 1. then load `$basePath."view.php"`.
 *      3. Determine if `$key` is a valid virtual path
 *          to a config value in 'view'.
 *      4. Return the configuration value.
 *
 * Examples
 *      `$reader = new ConfigFileReader(CONFIG);`
 *      `$enabled = $reader['view.twig.enabled']`; // returns a boolean
 *      `$settings = $reader['view.twig']; // returns the twig configuration array.
 */
class ConfigFileReader implements \ArrayAccess, Countable
{
    // provides ArrayAccess methods for accessing the Cache
    use WithCacheArrayAccess;
    use WithCacheImport;

    /**
     * The base path used when referencing config file locations.
     *
     * @var string
     */
    private $basePath;

    public function __construct(string $basePath = '')
    {
        $this->basePath = $basePath;
    }

    /**
     * Test whether a given key is cached.
     *
     * @param $key
     *
     * @return bool
     */
    public function cached($key)
    {
        return Lib::array_has($this->cache, $key);
    }

    /**
     * Simply empty the cache without respect for what might be there.
     */
    public function clearCache()
    {
        unset($this->cache);
        $this->cache = [];
    }

    /**
     * Count the cache keys.
     *
     * @return int
     */
    public function count()
    {
        return count($this->cache);
    }

    /**
     * Return the current base path setting.
     *
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * Set the base location where this instance can locate
     * configuration files.
     *
     * @param string $basePath
     *
     * @return ConfigFileReader
     */
    public function setBasePath(string $basePath): ConfigFileReader
    {
        $this->basePath = $basePath;

        return $this;
    }

    /**
     * Return a reference to the cache.
     *
     * @return array
     */
    public function getCache(): array
    {
        return $this->cache;
    }

    public function has(string $compoundKey)
    {
        return $this->offsetExists($compoundKey);
    }

    public function offsetSet($key, $value)
    {
        throw new UnsupportedUseOfArrayAccessMethod('ConfigFileReader forbids modifying configuration values. Use ConfigFileWriter instead.');
    }

    public function offsetUnset($key)
    {
        throw new UnsupportedUseOfArrayAccessMethod('ConfigFileReader forbids modifying configuration values. Use ConfigFileWriter instead.');
    }

    /**
     * Preload any configuration files found in the given path.
     *
     * IMPORTANT:
     *      This method is a polymorphic pre-loader, which means that
     *      it will load any files that match the `$mask`. `*.php` is
     *      the default.
     *
     *      Care should be taken to ensure that the mask does
     *      not return unexpected sources.
     *
     *      Also note that files are processed in alphabetical order.
     *
     *  `glob` is called on the directory path using the `$mask`.
     *      ie: `preloadPath(__DIR__ . '/../config/', '*.yaml') will parse
     *          all yaml files in the given directory.
     *
     * @param string $directoryPath
     * @param string $mask
     *
     * @return $this
     *
     * @throws InvalidConfigurationPathException
     * @throws InvalidArgumentException
     * @throws ParseException
     * @throws InvalidConfigurationImportValueException
     */
    public function preloadPath(string $directoryPath = NULL, string $mask = '*.php')
    {
        // if NULL is passed then assume the basePath.
        $directoryPath = $directoryPath ?: $this->basePath;

        if ( ! is_dir($directoryPath)) {
            throw new InvalidConfigurationPathException("Configuration path not found. (path: $directoryPath)");
        }

        // collect candidates from the path.
        // does not recurse directories.
        $files = glob(normalize_path($directoryPath) . $mask);

        // import each file based on its extension.
        // multiple file types are supported.
        foreach ($files as $filePath) {
            $extension = '.' . pathinfo($filePath, PATHINFO_EXTENSION);
            $key = pathinfo($filePath, PATHINFO_FILENAME);

            $this->importByExtension($extension, $filePath, $key);
        }

        return $this;

    }

    /**
     * Reads a configuration file and stores it in the cache.
     * Returns the section indicated by $section.
     * The entire file content is returned if $section is '*'.
     *
     * @param string $key  The key to the config data is the basename
     *                     of the config file.
     *                     i.e.: 'app' indicates 'app.php'|'app.yml'|'app.json'
     *
     * @return mixed Usually this returns an array but the key may reference
     *               a specific element of the containing array.
     *
     * @throws InvalidConfigurationImportValueException
     * @throws ParseException
     * @throws InvalidArgumentException
     * @throws ConfigurationFileNotFound
     */
    public function read(string $key)
    {
        // if the key does not exist then attempt loading the
        // related file from the basePath.
        if ( ! Lib::array_has($this->cache, $key)) {
            $this->importByPathOrKey($key);
        }

        return Lib::array_get($this->cache, $key);
    }

    /**
     * Read a configuration file.
     *
     * This method first attempts reading the configuration from the cache
     * using the filename (without path or extension) as the key. If not
     * in the cache then attempt locating in the given $filePath or the
     * class $basePath.
     *
     * Optional $filePath usages:
     *      * Filename without path or extension:
     *          Look for filename with '.php' extension in the basePath.
     *          ie: 'views' is interpreted as $basePath/views.php.
     *      * Fully qualified $filePath:
     *          Check that the file exists then import based on the extension.
     *          ie: full/path/to/view.ext reads a specific file and interprets
     *              it based on the extension.
     *      * Filename and Extension in $filePath:
     *          Look for the file in the $basePath.
     *          ie: 'view.yml' reads the file in $basePath/view.yml and
     *              interprets it as a YAML data structure.
     *
     * Examples:
     *      `$reader->readConfig('view')`
     *      `$reader->readConfig('view.yaml')`
     *      `$reader->readConfig('/home/dude/config/view.yaml')`
     *
     * @param string $filePathOrKey
     *
     *
     * @return $this|array|bool
     *
     * @throws InvalidArgumentException
     * @throws InvalidConfigurationImportValueException
     * @throws ParseException
     * @throws InvalidConfigurationPathException
     */
    public function readConfig(string $filePathOrKey)
    {
        $key = pathinfo($filePathOrKey, PATHINFO_FILENAME);

        if ($this->cached($key)) {
            return $this->cache[$key];
        }

        $ext = pathinfo($filePathOrKey, PATHINFO_EXTENSION);
        $ext = strlen($ext) > 0 ? ".$ext" : '.php';
        $filename = $key . $ext;

        $path = $this->basePath . $filePathOrKey;

        if (!file_exists($path)) {
            if (FALSE === ($path = Lib::file_in_path($filename, [pathinfo($filePathOrKey, PATHINFO_DIRNAME), $this->basePath]))) {
                throw new InvalidConfigurationPathException("Could not locate the provided file path. (path: $path)");
            }
        }

        $this->importByExtension($ext, $path, $key);

        return $this->cache[$key];
    }

    /**
     * Reads one or more config keys defined in an array.
     *
     * @param array $keys
     *
     * @return array
     *
     * @throws InvalidArgumentException
     * @throws \Nine\Loaders\Exceptions\ConfigurationFileNotFound
     * @throws \Nine\Loaders\Exceptions\InvalidConfigurationImportValueException
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    public function readMany(array $keys)
    {
        $collection = [];

        foreach ($keys as $key) {
            $this->read($key);
            $collection[$key] = $this->cache[$key];
        }

        return $collection;
    }

}
