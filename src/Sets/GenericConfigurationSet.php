<?php namespace Nine\Loaders\Sets;

use Nine\Loaders\ConfigFileReader;
use Nine\Loaders\ConfigurationSet;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
class GenericConfigurationSet extends ConfigurationSet
{
    public function __construct(string $key, ConfigFileReader $configFileReader)
    {
        parent::__construct($key, $configFileReader);
    }
}
