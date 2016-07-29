<?php

/**
 * @package Nine
 * @version 0.4.2
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

return [
    'base_path'      => VIEWS,
    'document_cache' => STORAGE . '/documents',
    'blade'          => [
        'enabled'  => TRUE,
        'defaults' => [
            'cache'          => CACHE . 'blade',
            'template_paths' => [
                VIEWS,
                VIEWS . 'assets/',
                VIEWS . 'templates/default/',
                VIEWS . 'templates/default/forms/',
                VIEWS . 'templates/default/pages/',
                VIEWS . 'templates/',
                VIEWS . 'debug/',
            ],
        ],
    ],
    #
    # see: <http://twig.sensiolabs.org/doc/api.html#twig-loader-filesystem>
    #
    'twig'           => [
        'enabled'  => TRUE,
        'defaults' =>
            [
                'type'       => 4, // twig loader will use files
                'filesystem' => [
                    # search local views first
                    VIEWS,
                    VIEWS . 'assets/',
                    VIEWS . 'templates/default/',
                    VIEWS . 'templates/default/forms/',
                    VIEWS . 'templates/default/pages/',
                    VIEWS . 'templates/',
                    VIEWS . 'debug/',

                ],
                'options'    => [
                    'cache'            => CACHE . 'twig',
                    'debug'            => env('DEBUG'),
                    'auto_reload'      => env('DEBUG'),
                    'strict_variables' => env('DEBUG'),
                ],
                'templates'  => [
                    // format is '<template_name>' => '<html_text_to_render>',
                ],
                'form'       => [
                    // format is '<template_name>' => '<html_text_to_render>',
                    'templates' => [
                        'bootstrap_3_horizontal_layout.html.twig',
                    ],
                ],
            ],
    ],
    'markdown'       => [
        'defaults' => [
            // class: Markdown, MarkdownExtra, GithubMarkdown
            'class'               => 'MarkdownExtra',
            'template_paths'      => [
                VIEWS . 'templates',
                VIEWS . 'templates/forms',
                VIEWS . 'templates/default',
                VIEWS . 'templates/default/hello',
            ],
            'debug'               => TRUE,
            'html5'               => TRUE,
            'keepListStartNumber' => TRUE,
        ],
    ],
    #
    # as required by Radium\View
    # prepended to the template service
    #
    # accessible through env(view_config)->view_defaults
    #
    'defaults'       => ['base_path' => VIEWS],
];
