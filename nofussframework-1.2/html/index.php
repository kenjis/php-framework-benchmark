<?php

error_reporting(E_ALL);

/*********************************************************
* Includes
* *******************************************************/
$libraryPath = realpath(dirname(__FILE__) . '/../vendor/nofussframework/nofussframework/Nf');
$applicationPath = realpath(dirname(__FILE__) . '/..');

/*********************************************************
* My application
* ********************************************************/
$applicationNamespace='App';

/*********************************************************
* Autoloader
* *******************************************************/
$nfAllFile = $applicationPath . '/cache/Nf.all.php';
if(file_exists($nfAllFile)) {
    require($nfAllFile);
}
else {
    require($libraryPath . '/Autoloader.php');
}
$autoloader=new \Nf\Autoloader();
$autoloader->addMap();
$autoloader->addNamespaceRoot('Nf', $libraryPath);
$autoloader->addNamespaceRoot($applicationNamespace, $applicationPath . '/models');
$autoloader->register();
/******************************************************* */

$bootstrap=new \Nf\Bootstrap($libraryPath, $applicationPath);
\Nf\Error\Handler::setErrorHandler();
$bootstrap->setApplicationNamespace($applicationNamespace);

$bootstrap->go();

printf(
    "\n%' 8d:%f",
    memory_get_peak_usage(true),
    microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
);