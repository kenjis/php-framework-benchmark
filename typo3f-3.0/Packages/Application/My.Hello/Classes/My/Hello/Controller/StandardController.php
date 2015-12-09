<?php
namespace My\Hello\Controller;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "My.Hello".              *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Mvc\Controller\ActionController;

class StandardController extends ActionController
{
	public function indexAction()
	{
		return 'Hello World!' . "\n" . require BENCHMARK_ROOT_PATH . '/libs/output_data.php';
	}
}
