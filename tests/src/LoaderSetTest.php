<?php namespace Nine\Loaders;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
use Nine\Loaders\Sets\AurynConfigurationSet;
use Nine\Loaders\Support\LoaderReflector;

/**
 * Test the Collection Class
 *
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class LoaderSetTest extends \PHPUnit_Framework_TestCase
{
    /** @var LoaderSet */
    protected $loader;

    /** @var ConfigFileReader */
    protected $reader;

    public function setUp()
    {
        $this->reader = new ConfigFileReader(CONFIG . 'loaders/');
        $this->loader = new LoaderSet(new LoaderReflector, 'test.loader');
    }

    public function test_configuration()
    {
        $reader = new ConfigFileReader(CONFIG . 'loaders/');
        $reader->readConfig('container.php');
        static::assertArrayHasKey('name', $reader->read('container.' . AurynConfigurationSet::class));

        $loader = new LoaderSet(new LoaderReflector, 'testing', $reader['container']);
        $loader->loadAll()->configure();

        //expose($loader->find('app.di')['set']['blade']);
    }

    public function test_instance()
    {
        static::assertInstanceOf(LoaderSet::class, $this->loader);
    }

}
