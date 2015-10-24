<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class HelloController extends AbstractActionController
{
    public function indexAction()
    {
        return $this->getResponse()->setContent('Hello World!');
    }
}
