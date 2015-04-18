<?php

/**
 * @global string $context
 */
namespace My\Hello;

use BEAR\AppMeta\AppMeta;
use BEAR\Package\Bootstrap;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\ApcCache;

load: {
    $dir = dirname(__DIR__);
    $loader = require $dir . '/vendor/autoload.php';
    AnnotationRegistry::registerLoader([$loader, 'loadClass']);
}

route: {
    /** @var $app \BEAR\Sunday\Extension\Application\AbstractApp */
    $app = (new Bootstrap)->newApp(new AppMeta(__NAMESPACE__), $context);
    $request = $app->router->match($GLOBALS, $_SERVER);
}

// Adhoc fix for benchmarking
// Remove sub directories from URI
$pagePath = preg_replace('!/php-framework-benchmark/bear-1.0/var/www/index.php!', '', $request->path);
//var_dump($pagePath); exit;

try {
    /** @var $page \BEAR\Resource\Request */
    $page = $app->resource
        ->{$request->method}
        ->uri($pagePath)
        ->withQuery($request->query)
        ->request();

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
