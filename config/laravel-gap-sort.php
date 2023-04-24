<?php

return [
    'sorting' => [
        /*
        * The name of the column that will be used to sort models.
        */
        'column' => 'order',

        /*
        * The gap between the sorted items
        */
        'gap' => 100,
    ],

    /*
    * Indicates wheter the "/sort" route will be automaticaly added when you use the route ::register method
    */
    'resource_registrar' => false,
];
