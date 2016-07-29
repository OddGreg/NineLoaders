<?php

/**
 * @package Nine
 * @version 0.4.2
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

use App\Controller\BaseController;
use Nine\Library;
use Nine\Library\Support;

// Use the BaseController to determine the controller namespace.
// This assumes that the BaseController namespace is the same as
// any other controller.
$base = Support::parse_class_name(BaseController::class);
$controller_namespace = implode("\\", $base['namespace']) . "\\";

return [
    //
    // -----------------------------------------------------------------
    // = Routing and Dispatch settings
    // -----------------------------------------------------------------
    //
    'controller_namespace' => $controller_namespace,
    //
    // -----------------------------------------------------------------
    // = Determine whether the Application uses/creates configuration
    // = caches.
    // -----------------------------------------------------------------
    //
    'use_cache'            => FALSE,
    'cache_path'           => CACHE,
    //
    // -----------------------------------------------------------------
    // = if the HttpCacheServiceProvider is registered, then use this
    // = as the default cache location.
    // -----------------------------------------------------------------
    //
    'http_cache_dir' => CACHE . 'http/',
];
