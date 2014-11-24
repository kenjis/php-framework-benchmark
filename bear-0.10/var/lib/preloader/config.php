<?php

use ClassPreloader\ClassLoader;

$appDir = dirname(dirname(dirname(__DIR__)));

$config = ClassLoader::getIncludes(
    function (ClassLoader $loader) use ($appDir) {
        $loader->register();
        $app = require $appDir . '/bootstrap/instance.php';
        (new \BEAR\Package\Dev\Application\ApplicationReflector($app))->compileAllResources();
    }
);

// Add a regex filter that requires that a class does not match the filter
$config
    ->addExclusiveFilter('/Doctrine\/Common\/Annotation/')
    ->addExclusiveFilter('/FirePHP/')
    ->addExclusiveFilter('/PHPParser_*/')
    ->addExclusiveFilter('/Smarty*/')
    ->addExclusiveFilter('/TokenParser/')
    ->addExclusiveFilter('/My.Hello\/Resource/')
    ->addExclusiveFilter('/AdapterTrait/');

return $config;
