<?php

namespace App;

// Create a dependency injector container
$di = new \Ice\Di();

// Register App namespace for App\Controller, App\Model, App\Library, etc.
$di->loader
    ->addNamespace(__NAMESPACE__, __DIR__)
    ->register();

// Set some service's settings
$di->dispatcher
    ->setNamespace(__NAMESPACE__);

$di->router
    ->setRoutes([
        ['GET', '/{controller:[a-z]+}/{action:[a-z]+[/]?}'],
        ['GET', '/{controller:[a-z]+[/]?}'],
        ['GET', ''],
    ]);

$di->view
    ->setViewsDir(__DIR__ . '/View/');

// Create and return a MVC application
return new \Ice\Mvc\App($di);
