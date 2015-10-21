<?php

return [
    // This can be used to seed pre- and/or post-routing middleware
    'middleware_pipeline' => [
        // An array of middleware to register prior to registration of the
        // routing middleware
        'pre_routing' => [
            //[
            // Required:
            //    'middleware' => 'Name of middleware service, or a callable',
            // Optional:
            //    'path'  => '/path/to/match',
            //    'error' => true,
            //],
        ],

        // An array of middleware to register after registration of the
        // routing middleware
        'post_routing' => [
            //[
            // Required:
            //    'middleware' => 'Name of middleware service, or a callable',
            // Optional:
            //    'path'  => '/path/to/match',
            //    'error' => true,
            //],
        ],
    ],
];
