<?php

namespace App\Controller;

class HelloController extends IndexController
{

    public function indexAction()
    {
        $this->view->setContent('Hello World!');
    }
}
