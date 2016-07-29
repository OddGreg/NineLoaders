<?php

/**
 * AuthServiceProvider Configuration
 *
 * @package Nine
 * @version 0.4.2
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

return [
    'security.firewalls' => [
        'admin' => [
            'pattern'   => '^/',
            'form'      => [
                'login_path' => '/login',
            ],
            'logout'    => TRUE,
            'anonymous' => TRUE,
            // admin user list -- this is for development only.
            'users'     => [env('SECURITY_USER') => [['ROLE_ADMIN', 'ROLE_USER'], env('SECURITY_PW')]],
        ],
    ],
];
