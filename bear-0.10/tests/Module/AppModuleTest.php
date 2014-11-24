<?php

namespace My\Hello\Module;

use Ray\Di\Injector;

class AppModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AppModule
     */
    private $module;

    /**
     * @var Injector
     */
    private $injector;

    protected function setUp()
    {
        static $module;
        static $injector;

        if (! $module) {
            $module = new AppModule('prod');
            $module->activate();
        }
        if (! $injector) {
            $injector = Injector::create([$module]);
        }
        $this->module = clone $module;
        $this->injector = clone $injector;
        parent::setUp();
    }

    public function testNew()
    {
        $this->assertInstanceOf('My\Hello\Module\AppModule', $this->module);
    }

    public function testApiContext()
    {
        $this->injector->setModule(new AppModule('api'));
        $actual = $this->injector->getInstance('BEAR\Resource\RenderInterface');
        $this->assertInstanceOf('BEAR\Package\Provide\ResourceView\HalRenderer', $actual);
    }

    public function testBindApp()
    {
        $actual = $this->injector->getInstance('BEAR\Sunday\Extension\Application\AppInterface');
        $this->assertInstanceOf('My\Hello\App', $actual);
    }
}
