<?php

namespace My\Hello\Module\App;

use BEAR\Package;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

class Dependency extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
         // $this->bind('My\Hello\FooInterface')->to('My\Hello\Foo');
    }
}
