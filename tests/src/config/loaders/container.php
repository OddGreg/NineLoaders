<?php

return [
    //AurynConfigurationSet::class      => [
    //    // the identifier given to this configuration set.
    //    'name'        => 'app.di',
    //    // the path to the folder that contains configuration files
    //    // for this set.
    //    'config_path' => CONFIG,
    //    // the loader priority.
    //    'priority'    => 'high', # 'high' | 'normal' | 'low' | int
    //    // the list of Configurators in this set.
    //    'config'      => [
    //        TwigConfigurator::class     => ['name' => 'twig', 'dataset' => 'view.twig'],
    //        //MarkdownConfigurator::class => ['name' => 'markdown', 'dataset' => 'view.markdown'],
    //    ],
    //],
    //IlluminateConfigurationSet::class => [
    //    'name'        => 'app.container',
    //    'config_path' => CONFIG,
    //    'priority'    => 'high',
    //    'config'      => [
    //        //BladeConfigurator::class    => ['name' => 'blade', 'dataset' => 'view.blade',],
    //        //TwigConfigurator::class     => ['name' => 'twig', 'dataset' => 'view.twig'],
    //        MarkdownConfigurator::class => ['name' => 'markdown', 'dataset' => 'view.markdown'],
    //    ],
    //],
    //ConfigurationSet::class => [
    'views' => [
        'name'        => 'app.views',
        'config_path' => CONFIG,
        'priority'    => 'high',
        'config'      => [
            BladeConfigurator::class    => ['name' => 'blade', 'dataset' => 'view.blade',],
            TwigConfigurator::class     => ['name' => 'twig', 'dataset' => 'view.twig'],
            MarkdownConfigurator::class => ['name' => 'markdown', 'dataset' => 'view.markdown'],
        ],
    ],
];
