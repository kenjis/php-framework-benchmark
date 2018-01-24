<?php
// src/AppBundle/Controller/HelloController.php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HelloController
{
    public function worldAction(Request $request)
    {
        return new Response('Hello World!');
    }
}
