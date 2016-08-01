<?php namespace Nine\Loaders;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Nine\Loaders\Configurators\Interfaces\ConfiguratorInterface;
use Nine\Loaders\Exceptions\PropertyIsInaccessibleException;
use Nine\Loaders\Interfaces\Prioritizable;
use Nine\Loaders\Support\Priority;
use Nine\Loaders\Traits\WithPrioritize;

/**
 * @property ConfigFileReader $files
 */
class Configurator implements ConfiguratorInterface, Prioritizable
{
    use WithPrioritize;

    /** @var bool */
    protected $configured = false;

    /** @var string */
    protected $dataset = '';

    /** @var string */
    protected $key;

    /** @var array */
    protected $settings;

    /**
     * Configurator constructor.
     *
     * @param string $name Identity of this set.
     * @param string $dataset
     * @param int    $priority
     * @param array  $config
     */
    public function __construct(string $name, string $dataset = '', int $priority = Priority::NORMAL, array $config = [])
    {
        $this->key = $name;
        $this->settings = $config;
        $this->dataset = $dataset;
        $this->priority = $priority;
    }

    /**
     * Limit magic property reading.
     *
     * @param $name
     *
     * @return ConfigFileReader
     * @throws PropertyIsInaccessibleException
     */
    public function __get($name)
    {
        throw new PropertyIsInaccessibleException("The property '$name' is not accessible.");
    }

    /**
     * Disable magic property writing.
     *
     * @param $name
     * @param $value
     *
     * @throws PropertyIsInaccessibleException
     */
    public function __set($name, $value)
    {
        throw new PropertyIsInaccessibleException("There are no writable properties available. (property: $name)");
    }

    /**
     * @return string
     */
    public function getDataset(): string
    {
        return $this->dataset;
    }

    /**
     * @param string $dataset
     *
     * @return Configurator
     */
    public function setDataset(string $dataset): Configurator
    {
        $this->dataset = $dataset;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * Descendant Configurators may override this method to handle specific
     * loading cases. By default, this method expects that the 'dataset'
     * parameter is set to the correct key for configuration.
     *
     * ie: key 'view.blade' indicates the view.php config file, index 'blade'.
     *
     * @param ConfigFileReader $config
     *
     * @return mixed|void
     */
    public function load(ConfigFileReader $config)
    {
        $this->settings = $this->settings += $config[$this->dataset];
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function mergeConfig(array $config)
    {
        $this->settings = $this->settings += $config;

        return $this;
    }

}
