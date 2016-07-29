<?php

return [
    //
    // -----------------------------------------------------------------
    // = Application Title and Version
    // -----------------------------------------------------------------
    //
    'title'                  => 'Formula 9',
    'version'                => '0.4.2',
    //
    // -----------------------------------------------------------------
    // = Environmental settings
    // -----------------------------------------------------------------
    //
    'timezone'               => 'America/Vancouver',
    'locale'                 => 'en',
    'mb_internal_encoding'   => 'UTF-8',
    'base_path'              => '',
    'asset_path'             => '/resources/assets',
    //
    // -----------------------------------------------------------------
    // = Fixed parameters for URL generation (may be overridden)
    // -----------------------------------------------------------------
    //
    'scheme'                 => 'http',
    'host'                   => 'localhost',
    'port'                   => '8080',
    //
    // -----------------------------------------------------------------
    // = Namespaced Controllers
    //
    //  Controllers are automatically injected as services by parsing
    //  the controllers located in the folder indicated by the
    //  CONTROLLERS constant.
    //
    //  However, you may need to add additional controllers that exist
    //  outside of the main controller namespace (as determined by the
    //  BaseController namespace).
    //
    //  Note: Automatically injected controllers determine their alias
    //        based on the name of the class. ie: DemoHelloController
    //        results in a service name of: demo.hello.controller. It
    //        is important not to use a service controller alias
    //        that conflicts with an already existing alias.
    // -----------------------------------------------------------------
    //
    'namespaced_controllers' =>
        [
            //'hello.controller' => DemoHelloController::class,
            //'form.controller'  => DemoFormController::class,
        ],
    //
    // -----------------------------------------------------------------
    // = Http Middleware
    // -----------------------------------------------------------------
    //
    'middleware'             =>
        [
        ],
    //
    // -----------------------------------------------------------------
    // = Framework and Application Service Providers
    // -----------------------------------------------------------------
    //
    'providers'              =>
        [
            /* REGISTER FIRST */

            // required for illuminate-based services
            F9\Support\Provider\IlluminateServiceProvider::class,
            // register early for access to debug functions
            F9\Support\Provider\TracyServiceProvider::class,
            // register for logging and reporting
            F9\Support\Provider\ReportingServiceProvider::class,
            // establish routing early
            F9\Support\Provider\RoutingServiceProvider::class,

            /* REGISTER NEXT */

            // use as required
            F9\Support\Provider\DatabaseServiceProvider::class,
            F9\Support\Provider\FormServiceProvider::class,
            F9\Support\Provider\ViewServiceProvider::class,
            F9\Support\Provider\AuthServiceProvider::class,
            F9\Support\Provider\PimpleDumpProvider::class,

            /* REGISTER LAST */

            App\Provider\ApplicationServiceProvider::class,

        ],
];
