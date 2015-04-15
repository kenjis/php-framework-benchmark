<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

$app->get('/', function() use ($app) {
	return $app->welcome();
});

$app->get('/php-framework-benchmark/lumen-5.0/public/index.php/hello/index', 'App\Http\Controllers\HelloController@index');
