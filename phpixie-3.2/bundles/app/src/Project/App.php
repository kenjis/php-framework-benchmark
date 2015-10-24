<?php

namespace Project;

class App implements \PHPixie\Bundles\Bundle\Provides\HTTPProcessor
{
    public function httpProcessor()
    {
        return new App\HTTPProcessor();
    }
    
    public function name()
    {
        return 'app';
    }
}