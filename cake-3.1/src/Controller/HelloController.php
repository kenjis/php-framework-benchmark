<?php

namespace App\Controller;

class HelloController extends AppController
{
    public $autoRender = false;

    public function index()
    {
        $this->response->body('Hello World!' . require dirname(__FILE__).'/../../../libs/output_data.php');
        return $this->response;
    }
}
