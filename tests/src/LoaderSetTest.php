<?php namespace Nine\Loaders;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
use Nine\Application\Containers\AurynDI;
use Nine\Loaders\Support\LoaderReflector;
use Nine\Loaders\Support\SymbolTable;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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

    /** @var SymbolTable $symbols */
    protected $symbols;

    public function setUp()
    {
        $di = new ContainerBuilder();
        $this->symbols = new SymbolTable([
            'container'             => SymbolTable::makeSymbol(AurynDI::class, $di),
            ContainerBuilder::class => SymbolTable::makeSymbol(AurynDI::class, $di),
        ]);

        $this->reader = new ConfigFileReader(CONFIG . 'loaders/');
        $this->loader = new LoaderSet('test.loader', new LoaderReflector($this->symbols), $di);
        $this->loader->setSymbolTable($this->symbols);
        $this->loader->import($this->reader['container']);
    }

    public function test_configuration()
    {
        $reader = new ConfigFileReader(CONFIG . 'loaders/');

        $reader->readConfig('container.php');
        static::assertArrayHasKey('name', $reader->read('container.views'));

        $this->loader->loadAll()->configure();
    }

    public function test_instance()
    {
        static::assertInstanceOf(LoaderSet::class, $this->loader);
    }

}
