<?php namespace Nine\Loader\Sets;

/**
 * @package Nine Loader
 * @version 0.5.0
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

interface SetCanRegister
{
    /**
     * Register any configurators with the set and/or register items with the DI.
     *
     * Example:
     *     $configurators = [
     *         \BladeConfigurator::class    => ['name' => 'blade', 'dataset' => 'view.blade', 'priority' => 'high'],
     *         \TwigConfigurator::class     => ['name' => 'twig', 'dataset' => 'view.twig'],
     *         \MarkdownConfigurator::class => ['name' => 'markdown', 'dataset' => 'view.markdown'],
     *     ];
     *
     *     foreach ($configurators as $abstract => $config) {
     *         $concrete = $this->insertWithCreate($abstract, $config);
     *         $this->container->set($config['name'], $concrete);
     *     }
     */
    public function register();
}
