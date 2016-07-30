<?php namespace Nine\Loaders;

use Auryn\Injector;
use Illuminate\Container\Container;
use Nine\Loaders\Exceptions\ConfiguratorNotFoundException;
use Nine\Loaders\Exceptions\DuplicateConfiguratorException;
use Nine\Loaders\Exceptions\KeyDoesNotExistException;
use Nine\Loaders\Exceptions\UnsupportedUseOfArrayAccessMethod;
use Nine\Loaders\Sets\AurynConfigurationSet;
use Nine\Loaders\Sets\GenericConfigurationSet;
use Nine\Loaders\Sets\IlluminateConfigurationSet;
use Nine\Loaders\Sets\PimpleConfigurationSet;
use Nine\Loaders\Sets\SymfonyDIConfigurationSet;
use Nine\Loaders\Support\Priority;
use Pimple\Container as PimpleContainer;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Test the Collection Class
 *
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class ConfigurationSetTest extends \PHPUnit_Framework_TestCase
{
    /** @var AurynConfigurationSet */
    protected $AurynSet;

    /** @var IlluminateConfigurationSet */
    protected $IlluminateSet;

    protected $InteropSet;

    /** @var PimpleConfigurationSet */
    protected $PimpleSet;

    /** @var ContainerBuilder $SymfonyDISet */
    protected $SymfonyDISet;

    /** @var ConfigurationSet */
    private $set;

    public function setUp()
    {
        $reader = new ConfigFileReader(CONFIG);

        $this->set = new GenericConfigurationSet('generic', $reader);
        $this->AurynSet = (new AurynConfigurationSet('auryn', $reader))->setContainer(new Injector);
        $this->PimpleSet = (new PimpleConfigurationSet('pimple', $reader))->setContainer(new PimpleContainer);
        $this->IlluminateSet = (new IlluminateConfigurationSet('illuminate', $reader))->setContainer(new Container);
        $this->SymfonyDISet = (new SymfonyDIConfigurationSet('symfony', $reader))->setContainer(new ContainerBuilder());
        //$this->InteropSet = new InteropConfigurationSet('interop', $reader, new Container);
    }

    public function test_configurator_set_priority()
    {
        $this->set->insert(new \BladeConfigurator('views.blade.configurator',
            'view.blade', Priority::NORMAL, ['enabled' => false]));

        $this->set->insert(new \MarkdownConfigurator('views.markdown.configurator',
            'view.markdown', Priority::LOW));

        $this->set->insert(new \TwigConfigurator('views.twig.configurator',
            'view.twig', Priority::HIGH));

        $this->set->load()->configure();

        $configurators = $this->set->getConfigurators();

        /** @noinspection PhpUndefinedMethodInspection */
        static::assertEquals(Priority::HIGH,
            array_shift($configurators)['configurator']->getPriority());

        /** @noinspection PhpUndefinedMethodInspection */
        static::assertEquals(Priority::NORMAL,
            array_shift($configurators)['configurator']->getPriority());

        /** @noinspection PhpUndefinedMethodInspection */
        static::assertEquals(Priority::LOW,
            array_shift($configurators)['configurator']->getPriority());
    }

    public function test_duplicate_add()
    {
        $this->set->insert(new \BladeConfigurator('views.blade.configurator'));
        $this->expectException(DuplicateConfiguratorException::class);
        $this->set->insert(new \BladeConfigurator('views.blade.configurator'));
    }

    public function test_get()
    {
        $this->set->insert($blade = new \BladeConfigurator('views.blade.configurator'));
        $this->set->insert($mark = new \MarkdownConfigurator('views.markdown.configurator'));
        $this->set->insert($twig = new \TwigConfigurator('views.twig.configurator'));

        static::assertSame($twig, $this->set->get('views.twig.configurator'));

        $this->expectException(ConfiguratorNotFoundException::class);
        $this->set->get('not.a.configurator');
    }

    public function test_loading()
    {
        $set = new GenericConfigurationSet('test', new ConfigFileReader(CONFIG));
        $set->load([
            new \TestConfigurator('test.configurator'),
            new \MarkdownConfigurator('views.markdown.configurator'),
            // TwigConfigurator depends on the correct dataset parameter.
            (new \TwigConfigurator('views.twig.configurator'))->setDataset('view.twig'),
        ]);

        static::assertTrue($set->has('test.configurator'));
        static::assertInstanceOf(\TestConfigurator::class, $set['test.configurator']);

        // failure test
        $set = new GenericConfigurationSet('test', new ConfigFileReader(CONFIG));

        $this->expectException(KeyDoesNotExistException::class);
        $set->load([
            // TwigConfigurator depends on the correct dataset parameter, so this should fail.
            new \TwigConfigurator('views.twig.configurator'),
        ]);

    }

    public function test_priority()
    {
        $set = new GenericConfigurationSet('test', new ConfigFileReader(CONFIG));
        $set->insert(new \TestConfigurator('test.configurator'));

        static::assertEquals(100, $set->get('test.configurator')->getPriority());
        $set->get('test.configurator')->setPriority('high');
    }

    public function test_set()
    {
        $set = new GenericConfigurationSet('test', new ConfigFileReader(CONFIG));
        $set->insert(new \TestConfigurator('test.configurator'));
        unset($set['test.configurator']);
        static::assertFalse($set->has('test.configurator'));
        static::assertFalse($set->offsetExists('test.configurator'));

        $this->expectException(UnsupportedUseOfArrayAccessMethod::class);
        // this should not be allowed
        $set['test'] = 'test';
    }

}
