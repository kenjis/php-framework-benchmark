<?php

namespace Acme\HelloBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class HelloController
{
    public function indexAction()
    {
        return new Response('Hello World!');
    }
}
