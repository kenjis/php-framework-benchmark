<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class HelloController extends Controller
{
    /**
     * @Route("/hello/index", name="hello_world")
     */
    public function indexAction()
    {
        return new Response('Hello World!' . require dirname(__FILE__).'/../../../../libs/output_data.php');
    }
}
