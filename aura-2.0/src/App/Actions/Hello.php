<?php

namespace App\Actions;

use Aura\Web\Request;
use Aura\Web\Response;

class Hello
{
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function __invoke()
    {
        $this->response->content->set(
            'Hello World!'
        );
    }
}
