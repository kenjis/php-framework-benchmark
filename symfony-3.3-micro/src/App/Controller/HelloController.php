<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class HelloController extends Controller
{
    /**
     * @return Response
     */
    public function indexAction()
    {
        return new Response('Hello World!');
    }
}
