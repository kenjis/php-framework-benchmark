<?php

namespace App\Controller;

class Hello
{
    public function index()
    {
        return 'Hello World!' . require dirname(__FILE__).'/../../../../libs/output_data.php';
    }
}
