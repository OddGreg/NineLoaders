<?php namespace Nine\Loaders\Traits;

/**
 * This trait exposes data import methods for an $cache property.
 *
 * @package Nine Traits
 * @version 0.4.2
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Nine\Library\Lib;
use Nine\Loaders\Exceptions\ConfigExportWriteFailure;
use Symfony\Component\Yaml\Yaml;

/**
 * WithCacheExport is a supplemental Trait and therefore expects that a
 * $cache property has already been defined. It cannot operate without it.
 *
 * @property array $cache
 */
trait WithCacheExport
{
    /**
     * @param string      $path
     * @param string      $key
     * @param string|null $baseName
     *
     * @throws ConfigExportWriteFailure
     */
    public function exportJsonFile(string $path, string $key, string $baseName = null)
    {
        $exportText = $key === '*'
            ? $this->formatJson($this->cache)
            : $this->formatJson($this->cache[$key]);

        $exportFilename = $this->buildExportFilename($path, $key, $baseName, '.json');
        $this->writeExportFile($exportFilename, $exportText);
    }

    /**
     * Export a part or the entirety of the collection to a PHP include file.
     *
     * @param string      $path      - the file to write
     * @param string      $key       - the block of data to write
     *                               - (use '*' to write the entire collection)
     * @param string|null $baseName  - the optional base filename
     *
     * @throws ConfigExportWriteFailure
     */
    public function exportPhpFile($path, $key, $baseName = null)
    {
        $export_structure = $key === '*'
            ? var_export($this->cache, true)
            : var_export($this->cache[$key], true);

        $exportFilename = $this->buildExportFilename($path, $key, $baseName);

        $exportText = "<?php \n return " . $export_structure . ';';
        $this->writeExportFile($exportFilename, $exportText);
    }

    /**
     * @param string      $path
     * @param string      $key
     * @param string|null $baseName
     *
     * @throws ConfigExportWriteFailure
     */
    public function exportYamlFile(string $path, string $key, string $baseName = null)
    {
        $exportText = $key === '*'
            ? Yaml::dump($this->cache, 4, 4)
            : Yaml::dump($this->cache[$key], 4, 4);

        $exportFilename = $this->buildExportFilename($path, $key, $baseName, '.yml');
        $this->writeExportFile($exportFilename, $exportText);
    }

    /**
     * @param        $path
     * @param        $key
     * @param        $baseName
     * @param string $extension
     *
     * @return string
     * @throws \Nine\Loaders\Exceptions\ConfigExportWriteFailure
     */
    protected function buildExportFilename($path, $key, $baseName, $extension = '.php')
    {
        $this->validatePath($path);
        $baseName = $this->generateExportBaseName($baseName, $key, $extension);

        return $path . $baseName;
    }

    /**
     * Export the entire collection contents to a json string.
     *
     * @param int  $options
     *
     * @param bool $readable
     *
     * @return string
     */
    protected function formatJson($options = 0, $readable = true)
    {
        return $readable
            ? Lib::encode_readable_json($this->cache, $options)
            : json_encode($options);
    }

    /**
     * @param string|null $baseName
     * @param string      $key
     * @param string      $extension
     *
     * @return string
     */
    protected function generateExportBaseName($baseName, string $key, string $extension = '.php')
    {
        if (null === $baseName) {
            return $key === '*' ? "export$extension" : $key . $extension;
        }

        return $baseName;
    }

    /**
     * @param $path
     *
     * @throws ConfigExportWriteFailure
     */
    protected function validatePath($path)
    {
        if ( ! is_dir($path)) {
            throw new ConfigExportWriteFailure("Path not found: `$path`");
        }
    }

    /**
     * @param $exportFilename
     * @param $exportText
     */
    protected function writeExportFile($exportFilename, $exportText)
    {
        if (file_exists($exportFilename)) {
            unlink($exportFilename);
        }

        file_put_contents($exportFilename, $exportText);
    }

}
