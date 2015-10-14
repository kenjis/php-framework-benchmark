<?php

/**
 * @global string $context
 */
namespace My\Hello;

use BEAR\Package\Bootstrap;
use Doctrine\Common\Annotations\AnnotationRegistry;

load: {
    /* @var $loader \Composer\Autoload\ClassLoader */
    $loader = require dirname(__DIR__) . '/vendor/autoload.php';
    AnnotationRegistry::registerLoader([$loader, 'loadClass']);
}

route: {
    $app = (new Bootstrap)->getApp(__NAMESPACE__, $context);
    /* @var $app AbstractApp \BEAR\Sunday\Extension\Application\AbstractApp */
    $request = $app->router->match($GLOBALS, $_SERVER);
}

// Adhoc fix for benchmarking
// Remove sub directories from URI
$pagePath = preg_replace('!/php-framework-benchmark/bear-1.0/var/www/index.php!', '', $request->path);
//var_dump($pagePath); exit;

try {
    // resource request
    $page = $app->resource
        ->{$request->method}
        ->uri($pagePath)
        ->withQuery($request->query)
        ->request();
    /* @var $page \BEAR\Resource\Request */

    // representation transfer
    $page()->transfer($app->responder, $_SERVER);
    printf(
        "\n%' 8d:%f",
        memory_get_peak_usage(true),
        microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
    );
    exit(0);
} catch (\Exception $e) {
    $app->error->handle($e, $request)->transfer();
    exit(1);
}
