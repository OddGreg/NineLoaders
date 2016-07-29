<?php

/**
 * @package Nine
 * @version 0.4.2
 * @author  Greg Truesdell <odd.greg@gmail.com>
 */

return [
    'files'     => NULL,
    'models'    => [
        'home'    => 'HomeModel',
        'contact' => 'ContactModel',
    ],
    'composers' => [
        'home'    => 'HomeModelComposer',
        'contact' => 'ContactModelComposer',
    ],
];
