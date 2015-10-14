<?php

namespace My\Hello;

use BEAR\Package\Bootstrap;
use BEAR\Sunday\Extension\Application\AbstractApp;

class AppModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider
     */
    public function contextsProvider()
    {
        return [
            ['app'],
            ['prod-hal-api-app'],
        ];
    }

    /**
     * @dataProvider contextsProvider
     */
    public function testNewApp($contexts)
    {
        $app = (new Bootstrap())->getApp(__NAMESPACE__, $contexts);
        $this->assertInstanceOf(AbstractApp::class, $app);
    }
}
