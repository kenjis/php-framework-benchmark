<?php

namespace My\Hello\Module;

use BEAR\AppMeta\AppMeta;
use BEAR\Package\PackageModule;
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new PackageModule(new AppMeta('My\Hello')));
    }
}
