<?php

/**
 * @package Nine
 * @version 0.4.2
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

return [

    // these settings are imported by the embedded artisan command
    'artisan'          => [
        'client_base' => realpath(__DIR__ . '/..'),
        'paths'       =>
            [
                'database'   => '/db',
                'migrations' => '/db/migrations',
                'models'     => '/db/models',
                'seeds'      => '/db/seeds',
            ],
    ],

    // name of the migration table
    'migrations'       => 'migrations',

    // fetch mode used by the PDO layer. Probably should not change,
    // but have at it.
    'fetch'            => PDO::FETCH_CLASS,
    'fetchClass'       => stdClass::class,
    'persistent'       => TRUE,

    // the default database connection set
    'default'          => env('DB_CONNECTION', 'sqlite'),

    // PDO Database enablers
    'database_enabled' => TRUE,

    // eloquent enablers
    'eloquent_enabled' => TRUE,

    // these depend on eloquent_enabled.
    'logging'          => TRUE,
    'eloquent_global'  => TRUE,

    // database engine
    // ---------------
    // Nine\Database\Database::class -or- Nine\Database\NineBase::class
    'database_engine'  => Nine\Database\NineBase::class,

    // connection sets available to the application.
    'connections'      => [

        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => __DIR__ . '/database.sqlite',
            //'database' => database_path() . '/database.sqlite',
            'prefix'   => '',
        ],

        'default' => [
            'driver'    => 'mysql',
            //'dsn'       => 'mysql:host=' . env('DB_HOST', 'localhost') . ';dbname=' . env('DB_DATABASE', 'forge'),
            'host'      => env('DB_HOST', 'localhost'),
            'database'  => env('DB_DATABASE', 'forge'),
            'username'  => env('DB_USERNAME', 'forge'),
            'password'  => env('DB_PASSWORD', ''),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => FALSE,
            'logging'   => env('DEBUG') === TRUE,
        ],

        'mysql' => [
            'driver'    => 'mysql',
            'dsn'       => 'mysql:host=' . env('DB_HOST', 'localhost') . ';dbname=' . env('DB_DATABASE', 'forge'),
            'host'      => env('DB_HOST', 'localhost'),
            'database'  => env('DB_DATABASE', 'forge'),
            'username'  => env('DB_USERNAME', 'forge'),
            'password'  => env('DB_PASSWORD', ''),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => FALSE,
            'logging'   => env('DEBUG') === TRUE,
        ],

        'meta_andro' => [
            'driver'    => 'mysql',
            'dsn'       => 'mysql:host=' . env('DB_HOST', 'localhost') . ';dbname=meta_andro',
            'host'      => 'localhost',
            'database'  => 'meta_andro',
            'username'  => env('DB_USERNAME', 'unknown'),
            'password'  => env('DB_PASSWORD', ''),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'strict'    => FALSE,
        ],
    ],

];
