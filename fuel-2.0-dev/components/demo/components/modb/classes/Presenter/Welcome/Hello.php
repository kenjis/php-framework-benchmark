<?php
/**
 * @package    demo-application
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Modb\Presenter\Welcome;

use Fuel\Display\Presenter;

/**
 * The Welcome Hello view model.
 *
 * @package  demo-application
 * @extends  Fuel\Display\Presenter
 */
class Hello extends Presenter
{
	/**
	 * Prepare the view data, keeping this in here helps clean up
	 * both the controller and the view.
	 *
	 * @return void
	 */
	public function view()
	{
		$this->name = $this->request->getParam('name', 'World');
	}
}
