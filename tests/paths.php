<?php

if ( ! defined('ROOT')) {
    // one root to serve them all
    define('ROOT', dirname(__DIR__) . '/');
    //@formatter:off
    define('API',           ROOT      . 'app/api/');
    define('APP',           ROOT      . 'app/');
    define('BOOT',          ROOT      . 'boot/');
    define('CACHE',         ROOT      . 'cache/');
    define('CONFIG',        ROOT      . 'tests/src/config/');
    define('DOMAIN',        APP       . 'Actors/');
    define('DATABASE',      ROOT      . 'db/');
    define('CONTROLLERS',   DOMAIN    . 'Controllers/');
    define('FRAMEWORK',     ROOT      . '');
    define('LOGS',          ROOT      . 'logs/');
    define('RESOURCES',     APP       . 'Resources/');
    define('STORAGE',       ROOT      . 'local/storage/');
    define('VENDOR',        ROOT      . '../vendor/');
    define('PACKAGES',      ROOT      . 'vendor/');
    define('VIEWS',         RESOURCES . 'views/');

    /** relative paths */
    define('ASSETS',                    '/resources/assets/');
    define('FONTS',         ASSETS    . 'fonts/');
    define('IMAGES',        ASSETS    . '/images/');
    define('SCRIPTS',       ASSETS    . 'js/');
    define('STYLESHEETS',   ASSETS    . 'css/');
    //@formatter:on

    define('APP_NAMESPACE', 'App\\Controller\\');

}

/** @noinspection RealpathOnRelativePathsInspection */
return ($paths = [
    'controllers' => CONTROLLERS,
    'app'         => APP,
    'api'         => API,
    'boot'        => BOOT,
    'boot.assets' => BOOT . 'assets/',
    'cache'       => CACHE,
    'config'      => CONFIG,
    'database'    => DATABASE,
    'db'          => DATABASE,
    'logs'        => LOGS,
    'public'      => ROOT . 'public/',
    'resources'   => RESOURCES,
    'root'        => ROOT,
    'storage'     => STORAGE,
    'views'       => VIEWS,
    /** Framework Packages Roots */
    'vendor'      => VENDOR
]);
