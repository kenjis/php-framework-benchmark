<?php
namespace Project;

class Framework extends \PHPixie\BundleFramework
{
    protected function buildBuilder()
    {
        return new Framework\Builder();
    }
}