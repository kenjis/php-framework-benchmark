<?php

class HelloController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        return $this->response->setContent('Hello World!');
    }
}
