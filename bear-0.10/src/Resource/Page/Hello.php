<?php

namespace My\Hello\Resource\Page;

use BEAR\Resource\ResourceObject;

class Hello extends ResourceObject
{
    public function onGet()
    {
        $this->body = 'Hello World!';
        return $this;
    }
}
