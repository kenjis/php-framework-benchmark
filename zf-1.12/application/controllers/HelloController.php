<?php

class HelloController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->view->String = 'Hello World!';
    }
}
