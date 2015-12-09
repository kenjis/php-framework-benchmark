<?php

namespace App\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HelloAction
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next)
    {
        $response->getBody()->write('Hello World!' . require dirname(__FILE__).'/../../../libs/output_data.php');
        return $response->withHeader('Content-Type', 'text/html');
    }
}
