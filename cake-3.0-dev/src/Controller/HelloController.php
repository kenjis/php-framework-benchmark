<?php

namespace App\Controller;

class HelloController extends AppController
{
    public $uses = [];
    public $autoRender = false;

    public function index()
    {
        echo 'Hello World!';
    }
}
