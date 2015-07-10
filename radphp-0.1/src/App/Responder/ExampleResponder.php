<?php

namespace App\Responder;

/**
 * Example Responder
 *
 * @package App\Responder
 */
class ExampleResponder extends AppResponder
{
    public function getMethod()
    {
        $hello = 'Hello World!';

        $hello .= sprintf(
            "\n%' 8d:%f",
            memory_get_peak_usage(true),
            microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
        );

        $this->response->setContent($hello);
    }
}
