<?php

/**
 * @package Nine
 * @version 0.4.2
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

return [
    'name'      => [
        'first'   => 'James',
        'initial' => 'H',
        'last'    => 'Horner',
    ],
    'address'   => [
        'address'  => '123 14th Ave E.',
        'city'     => 'Vancouver',
        'province' => 'BC',
        'country'  => 'Canada',
        'postal'   => 'A1B 2C3',
    ],
    'telephone' => [
        'land'   => 'n/a',
        'mobile' => '789-011-2345',
    ],
    'title'     => 'Blade Test Template',
    'blade'     => [
        'cache'          => __DIR__ . '/cache',
        'template_paths' => [
            __DIR__ . '/templates',
        ],
    ],
    'twig'      => [
        'type'        => 4, // twig loader will use files
        'filesystem'  => [
            __DIR__ . '/templates',
        ],
        'environment' => [
            'cache'       => __DIR__ . '/cache',
            'debug'       => TRUE,
            'auto_reload' => TRUE,
        ],
    ],
    'markdown'  => [
        'debug'               => TRUE,
        'class'               => 'MarkdownExtra', // options: Markdown, MarkdownExtra, GithubMarkdown
        'template_paths'      => [
            //ROOT . 'tests/templates/markdown',
            __DIR__ . '/templates',
        ],
        'html5'               => TRUE,
        'keepListStartNumber' => TRUE,
        'enableNewLines'      => FALSE,  // only significant if the class is GithubMarkdown.

    ],
    'sets'      => [
        'views' => [
            // the identifier given to this configuration set.
            'name'        => 'app.di',
            // the path to the folder that contains configuration files
            // for this set.
            'config_path' => CONFIG,
            // the loader priority.
            'priority'    => 'high', # 'high' | 'normal' | 'low' | integer(1..1024),
            // the list of Configurators in this set.
            'config'      => [
                // the identifier for this Configurator
                'view.blade' => [
                    // the configurator
                    BladeConfigurator::class => [
                        // the data set loaded by the ConfigFileReader class
                        // defaults to [] if not supplied.
                        'dataset'  => 'view.blade',
                        // the set priority. Defaults to 'normal' if not supplied.
                        'priority' => 'normal',
                        // any settings to add or to override settings from the data set.
                        // defaults to [] if not supplied.
                        'config'   => [],
                    ],
                ],
            ],
        ],
    ],
];
