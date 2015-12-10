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
        $response->getBody()->write('Hello World!');
        return $response->withHeader('Content-Type', 'text/html');
    }
}
