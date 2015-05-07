<?php

namespace App\Controller;

class HelloController extends IndexController
{

    public function indexAction()
    {
        return $this->response->setContent('Hello World!');
    }
}
