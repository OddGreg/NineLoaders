<?php

use Nine\Loaders\Sets\AurynConfigurationSet;
use Nine\Loaders\Sets\IlluminateConfigurationSet;
use Nine\Loaders\Sets\SymfonyDIConfigurationSet;

return [
    AurynConfigurationSet::class      => [
        // the identifier given to this configuration set.
        'name'        => 'app.di',
        // the path to the folder that contains configuration files
        // for this set.
        'config_path' => CONFIG,
        // the loader priority.
        'priority'    => 'high', # 'high' | 'normal' | 'low' | int
        // the list of Configurators in this set.
        'config'      => [
            // the configurator
            BladeConfigurator::class    => [
                // the identifier for this Configurator
                'name'     => 'blade',
                // the data set loaded by the ConfigFileReader class
                // defaults to [] if not supplied.
                'dataset' => 'view.blade',
                // the set priority. Defaults to 'normal' if not supplied.
                'priority' => 'high',
                // any settings to add or to override settings from the data set.
                // defaults to [] if not supplied.
                'config'   => ['cargo' => 'shamalam'],
            ],
            TwigConfigurator::class     => ['name' => 'twig', 'dataset' => 'view.twig'],
            MarkdownConfigurator::class => ['name' => 'markdown', 'dataset' => 'view.markdown'],
        ],
    ],
    IlluminateConfigurationSet::class => [
        'name'        => 'app.container',
        'config_path' => CONFIG,
        'priority'    => 'high',
        'config'      => [
            //BladeConfigurator::class    => ['name' => 'blade', 'dataset' => 'view.blade',],
            TwigConfigurator::class     => ['name' => 'twig', 'dataset' => 'view.twig'],
            MarkdownConfigurator::class => ['name' => 'markdown', 'dataset' => 'view.markdown'],
        ],
    ],
    SymfonyDIConfigurationSet::class => [
        'name'        => 'app.symfony',
        'config_path' => CONFIG,
        'priority'    => 'high',
        'config'      => [
            //BladeConfigurator::class    => ['name' => 'blade', 'dataset' => 'view.blade',],
            TwigConfigurator::class     => ['name' => 'twig', 'dataset' => 'view.twig'],
            MarkdownConfigurator::class => ['name' => 'markdown', 'dataset' => 'view.markdown'],
        ],
    ],
];
