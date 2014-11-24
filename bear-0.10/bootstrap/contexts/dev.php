<?php

use BEAR\Package\Dev\Dev;

/**
 * CLI / Built-in web server script for development
 *
 * This script is the entry point for CLI an application whilst in development. When in production look
 * at index.php. This script is a base guideline and this procedural boot strap is gives you some defaults
 * as a guide. You are free to change and configure this script at will.
 *
 * CLI:
 * $ php web.php get /
 *
 * Built-in web server:
 * $ php -S localhost:8080 web.php
 *
 * @global $context string
 */

ob_start();

// Serve file as is in built in wev-server.
if (php_sapi_name() === 'cli-server' && preg_match('/\.(?:png|jpg|jpeg|gif|js|txt|css)$/', $_SERVER["REQUEST_URI"])) {
    return false;
}

$appDir = dirname(dirname(__DIR__));

//
// The cache is cleared on each request via the following script. We understand that you may want to debug
// your application with caching turned on. When doing so just comment out the following.
//
//require $appDir . '/bin/clear.php';

// Here we get an application instance by setting a $context variable such as (prod, dev, api)
// the dev instance provides debugging tools and defaults to help you the development of your application.
$context = 'dev';
$app = require $appDir . '/bootstrap/instance.php';
/* @var $app \BEAR\Package\Provide\Application\AbstractApp */

$devHtml = (new Dev)
    ->iniSet()
    ->registerErrorHandler()
    ->registerFatalErrorHandler()
    ->registerExceptionHandler("{$appDir}/var/log")
    ->registerSyntaxErrorEdit()
    ->setApp($app, $appDir)
    ->getDevHtml();
if ($devHtml) {
    http_response_code(200);
    echo $devHtml;
    exit(0);
}

//
// Calling the match of a BEAR.Sunday compatible router will give us the $method, $pagePath, $query to be used
// in the page request.
//
list($method, $pagePath, $query) = $app->router->match();

//
// An attempt to request the page resource is made along with setting the response with the resource itself.
// Upon failure the exception handler will be triggered.
//
try {
    $app->page = $app->resource->$method->uri('page://self' . $pagePath)->withQuery($query)->eager->request();
    $app->response->setResource($app->page)->render()->send();
    exit(0);
} catch (Exception $e) {
    $app->exceptionHandler->handle($e);
    exit(1);
}
