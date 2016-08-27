<?php declare(strict_types = 1);

namespace Nine\Loaders;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Nine\Library\Lib;
use Nine\Loaders\Traits\WithCacheArrayAccess;
use Nine\Loaders\Traits\WithCacheExport;
use Nine\Loaders\Traits\WithCacheImport;

/**
 * ConfigFileWriter provides methods for altering and exporting config
 * settings and files.
 *
 * Usage:
 *      // create and preload a collection of configurations.
 *      $reader = (new ConfigFileReader(CONFIG))->preloadPath();
 *
 *      // create the writer containing pre-loaded configurations.
 *      $writer = new ConfigFileWriter($reader);
 *
 *      // modify the configuration
 *      $writer['view.markdown.default.debug'] = false;
 *
 *      // extract an outer key array and write it to a new file
 *      $writer->exportPHPFile(
 *          // the path to where the new file will be written
 *          CONFIG,
 *          // the outer key to extract and write (ie: ['view'] -> 'CONFIG/view.php')
 *          // optionally, this can be set to '*' to export the entire cache.
 *          'view',
 *          // optionally, force the filename to something other than the key
 *          'production.php'
 *      );
 *
 */
class ConfigFileWriter implements \ArrayAccess
{
    use WithCacheArrayAccess;
    use WithCacheExport;
    use WithCacheImport;

    protected $basePath = '';

    /**
     * The ConfigFileReader is referenced mainly for access to
     * its cache and base path setting.
     *
     * @var ConfigFileReader
     */
    private $reader;

    public function __construct(ConfigFileReader $reader)
    {
        $this->reader = $reader;
        $this->cache = $reader->getCache();
        $this->basePath = $reader->getBasePath();
    }

    /**
     * @return array
     */
    public function getCache() : array
    {
        return $this->cache;
    }

    /**
     * @param string $compoundKey
     * @param        $value
     */
    public function __set(string $compoundKey, $value)
    {
        Lib::array_set($this->cache, str_replace('_', '.', $compoundKey), $value);
    }

    /**
     * @param mixed $compoundKey
     * @param mixed $value
     */
    public function offsetSet($compoundKey, $value)
    {
        $this->set($compoundKey, $value);
    }

    /**
     * @param string $compoundKey
     * @param        $value
     */
    public function set(string $compoundKey, $value)
    {
        Lib::array_set($this->cache, $compoundKey, $value);
    }

}
