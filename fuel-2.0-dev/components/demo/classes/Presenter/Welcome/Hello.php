<?php
/**
 * @package    demo-application
 * @version    2.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Demo\Presenter\Welcome;

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
		// if we didn't have a name passed, make one up
		if ( ! isset($this->name))
		{
			$this->name = 'World';
		}
	}
}
