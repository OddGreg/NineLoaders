<?php namespace Nine\Loaders;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use Nine\Loaders\Interfaces\ConfiguratorInterface;
use Nine\Loaders\Interfaces\Prioritizable;
use Nine\Loaders\Support\Priority;
use Nine\Loaders\Traits\WithImmutableGetSet;
use Nine\Loaders\Traits\WithPrioritize;

/**
 * @property ConfigFileReader $files
 */
class Configurator implements ConfiguratorInterface, Prioritizable
{
    use WithPrioritize;
    use WithImmutableGetSet;

    /** @var bool */
    protected $configured = FALSE;

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
     * Returns the dataset key (into the ConfigFileReader data.)
     *
     * @return string
     */
    public function getDataset(): string
    {
        return $this->dataset;
    }

    /**
     * Optional method for setting the configurator dataset property.
     * This is almost never necessary.
     *
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
     * Return the key (name) of the current configurator.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Return the configuration settings for this configurator.
     *
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
