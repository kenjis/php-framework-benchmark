<?php

namespace My\Hello\Module\App;

use BEAR\Package;
use Ray\Di\AbstractModule;

class Aspect extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        /*
        $this->bindInterceptor(
             $this->matcher->any(),
             $this->matcher->annotatedWith('My\Hello\Annotation\Bar'),
             [$this->requestInjection('My\Hello\Interceptor\FooInterceptor')]
        );
        */
    }
}
