<?php

return [
    'dependencies' => [
        'invokables' => [
            Zend\Expressive\Router\RouterInterface::class => Zend\Expressive\Router\FastRouteRouter::class,
        ],
    ],

    'routes' => [
        [
            'name' => 'home',
            'path' => '/',
            'middleware' => App\Action\HomePageAction::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'api.ping',
            'path' => '/api/ping',
            'middleware' => App\Action\PingAction::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'hello.subdirectory',
            'path' => '/php-framework-benchmark/ze-1.0/public/index.php/hello/index',
            'middleware' => App\Action\HelloAction::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'hello',
            'path' => '/ze-1.0/public/index.php/hello/index',
            'middleware' => App\Action\HelloAction::class,
            'allowed_methods' => ['GET'],
        ],
    ],
];
