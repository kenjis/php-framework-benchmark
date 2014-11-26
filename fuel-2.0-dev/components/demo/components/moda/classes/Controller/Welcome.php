<?php
/**
 * @package    Fuel\Foundation
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Moda\Controller;

class Welcome extends \Fuel\Controller\Base
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
	 * A typical "Hello, Bob!" type example.  This uses a Presenter to
	 * show you how to use them.
	 *
	 * @access  public
	 * @return  Presenter
	 */
	public function actionHello()
	{
		return \Presenter::forge('welcome/hello')
			->set('name', $this->request->getParam('name', 'Universe'));
	}

	/**
	 * The 404 action for the application.
	 *
	 * @access  public
	 * @return  Response
	 */
	public function action404()
	{
		return \Response::forge(\Presenter::forge('welcome/error404', null, null, 'welcome/404'), 404);
	}

}
