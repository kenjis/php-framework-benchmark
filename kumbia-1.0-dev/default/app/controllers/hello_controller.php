<?php

class HelloController extends AppController
{
    public function index()
    {
        View::select(null, null);
        echo 'Hello World!';
    }
}
