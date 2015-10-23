<?php

namespace Project\App;

class HTTPProcessor implements \PHPixie\Processors\Processor
{
    public function process($request)
    {
        return 'Hello World!';
    }
}
