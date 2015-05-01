<?php
namespace Apps\Controllers;

use Cygnite\Mvc\Controller\AbstractBaseController;

class HelloController extends AbstractBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function indexAction()
    {
        echo 'Hello World!';
    }
}