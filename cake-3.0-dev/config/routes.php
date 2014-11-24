<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Core\Plugin;
use Cake\Routing\Router;

Router::scope('/', function ($routes) {
/**
 * Here, we are connecting '/' (base path) to a controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, src/Template/Pages/home.ctp)...
 */
	$routes->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);

/**
 * ...and connect the rest of 'Pages' controller's URLs.
 */
	$routes->connect('/pages/*', ['controller' => 'Pages', 'action' => 'display']);

/**
 * Connect a route for the index action of any controller.
 * And a more general catch all route for any action.
 *
 * The `fallbacks` method is a shortcut for
 *    `$routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'InflectedRoute']);`
 *    `$routes->connect('/:controller/:action/*', [], ['routeClass' => 'InflectedRoute']);`
 *
 * You can remove these routes once you've connected the
 * routes you want in your application.
 */
	$routes->fallbacks();
});

/**
 * Load all plugin routes.  See the Plugin documentation on
 * how to customize the loading of plugin routes.
 */
	Plugin::routes();
