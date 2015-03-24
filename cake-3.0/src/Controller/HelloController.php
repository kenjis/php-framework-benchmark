<?php

namespace App\Controller;

class HelloController extends AppController
{
    public $autoRender = false;

    public function index()
    {
        echo 'Hello World!';
    }
}
