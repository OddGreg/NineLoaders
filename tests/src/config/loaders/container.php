<?php

use Nine\Loader\Sets\ExampleConfigurationSet;

return [
    ExampleConfigurationSet::class => [
        'name'        => 'view.config',
        'config_path' => CONFIG,
        'priority'    => 'normal',
    ],
    'views'                        => [
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
