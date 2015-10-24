<?php

namespace Project\Framework;

class Builder extends \PHPixie\BundleFramework\Builder
{
    protected function buildBundles()
    {
        return new Bundles($this);
    }
    
    protected function buildExtensions()
    {
        return new Extensions($this);
    }
    
    protected function getRootDirectory()
    {
        return realpath(__DIR__.'/../../../');
    }
}