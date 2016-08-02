<?php namespace Nine\Loader\Sets;

use Nine\Loaders\ConfigurationSet;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */
class ExampleConfigurationSet extends ConfigurationSet implements SetCanRegister
{
    /** @var ContainerBuilder $container */
    protected $container;

    /**
     * Register any configurators with the set and/or register items with the DI.
     */
    public function register()
    {
        # the configurators we want to insert into the set.
        $configurators = [
            \BladeConfigurator::class    => ['name' => 'blade', 'dataset' => 'view.blade', 'priority' => 'high'],
            \TwigConfigurator::class     => ['name' => 'twig', 'dataset' => 'view.twig'],
            \MarkdownConfigurator::class => ['name' => 'markdown', 'dataset' => 'view.markdown'],
        ];

        # insert and then register with the DI container.
        foreach ($configurators as $abstract => $config) {
            $concrete = $this->insertWithCreate($abstract, $config);
            $this->container->set($config['name'], $concrete);
        }
    }
}
