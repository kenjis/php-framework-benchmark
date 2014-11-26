<?php
/**
 * @package    demo-application
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Demo\Controller;

class Welcome extends Helpers\Base
{
	/**
	 * The basic welcome message
	 *
	 * @access  public
	 * @return  View
	 */
	public function actionIndex()
	{
		return \View::forge('welcome/index');
	}

	/**
	 * A typical "Hello, Bob!" type example.  This uses named parameters in
	 * the route, and a Presenter to show you how to use them.
	 *
	 * @access  public
	 * @return  Presenter
	 */
	public function actionHello()
	{
		return \Presenter::forge('welcome/hello')
			->set('name', $this->request->getParam('name', 'World'));
	}

	/**
	 * The 404 action for the application.
	 *
	 * @access  public
	 * @return  Response
	 */
	public function action404()
	{
		return \Response::forge('html', \Presenter::forge('welcome/error404', null, null, 'welcome/404'), 404);
	}

}
