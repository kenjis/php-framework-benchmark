<?php

namespace App\Controller;

class HelloController extends AppController
{
    public $autoRender = false;

    public function index()
    {
        $this->response->body('Hello World!');
        return $this->response;
    }
}
