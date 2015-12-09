<?php

namespace App\Http\Controllers;

class HelloController extends Controller
{
    public function index()
    {
        return 'Hello World!' . require dirname(__FILE__).'/../../../../libs/output_data.php';
    }
}
