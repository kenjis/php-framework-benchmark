<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://wiki.kumbiaphp.com/Licencia
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@kumbiaphp.com so we can send you a copy immediately.
 *
 * @category   Kumbia
 * @package    Session
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

use \Mockery as m;

/**
 * @category Test
 *
 * @runTestsInSeparateProcesses
 */
class HtmlTest extends PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        /*
         * Cuando se ejecutan los procesos por separado (@runTestsInSeparateProcesses)
         * es recomendado cerrar Mockery en el tearDown
         *
         * http://docs.mockery.io/en/latest/reference/phpunit_integration.html#phpunit-integration
         */
        m::close();
    }

    public function imgDataProvider()
    {
        return array(
            array(
                'img' => 'img.jpg',
                'alt' => null,
                'attrs' => array('class="btn"', 'class="btn"'),
                'expected' => sprintf('<img src="%simg/img.jpg" alt="" class="btn" />', PUBLIC_PATH),
            ),
            array(
                'img' => 'path/to/img2.png',
                'alt' => 'Image Name',
                'attrs' => array(array('class' => 'btn'), 'class="btn"'),
                'expected' => sprintf('<img src="%simg/path/to/img2.png" alt="Image Name" class="btn" />', PUBLIC_PATH),
            ),
            array(
                'img' => 'path/to/img2.png',
                'alt' => 'Alt',
                'attrs' => array(array('class' => 'btn btn-primary', 'target' => '_blank'), 'class="btn" target="_blank"'),
                'expected' => sprintf('<img src="%simg/path/to/img2.png" alt="Alt" class="btn" target="_blank" />', PUBLIC_PATH),
            ),
        );
    }

    /**
     * @dataProvider imgDataProvider
     */
    public function testImg($img, $alt, $attrs, $expected)
    {
        $tagMock = m::mock('alias:Tag');
        $tagMock->shouldReceive('getAttrs')->withArgs(array($attrs[0]))->andReturn($attrs[1]);

        $this->assertSame($expected, Html::img($img, $alt, $attrs[0]));
    }

    public function testImgDefaultAlt()
    {
        $tagMock = m::mock('alias:Tag');
        $tagMock->shouldReceive('getAttrs')->withAnyArgs()->andReturn('');

        $expected = sprintf('<img src="%simg/img.png" alt=""  />', PUBLIC_PATH);
        $this->assertSame($expected, Html::img('img.png'));
    }

    public function testLink()
    {
        $tagMock = m::mock('alias:Tag');
        $tagMock->shouldReceive('getAttrs')->with(array('a' => 'b'))->andReturn('a="b"');
        $tagMock->shouldReceive('getAttrs')->with(array('a' => 'b', 'c' => 'd'))->andReturn('a="b" c="d"');

        $expected = sprintf('<a href="%saction-name"  >Action name</a>', PUBLIC_PATH);
        $this->assertSame($expected, Html::link('action-name', 'Action name'));

        $expected = sprintf('<a href="%saction-name" a="b" >Action name</a>', PUBLIC_PATH);
        $this->assertSame($expected, Html::link('action-name', 'Action name', array('a' => 'b')));

        $expected = sprintf('<a href="%saction-name" a="b" c="d" >Action name</a>', PUBLIC_PATH);
        $this->assertSame($expected, Html::link('action-name', 'Action name', array('a' => 'b', 'c' => 'd')));
    }

    public function testLinkWithoutAttrs()
    {
        $tagMock = m::mock('alias:Tag');
        $tagMock->shouldNotReceive('getAttrs');

        Html::link('action-name', 'Action name');
        Html::link('action-name', 'Action name', 'a="b"');
    }

    public function testLinkWithAttrsAsArray()
    {
        $tagMock = m::mock('alias:Tag');
        $tagMock->shouldReceive('getAttrs')->times(3);

        Html::link('action-name', 'Action name');
        Html::link('action-name', 'Action name', 'a="b"');

        Html::link('action-name', 'Action name', array());
        Html::link('action-name', 'Action name', array('a' => 'b'));
        Html::link('action-name', 'Action name', array('a' => 'b', 'c' => 'd'));
    }

    public function linkActionDataProvider()
    {
        return array(
            array('action', 'controller', sprintf('href="%scontroller/action"', PUBLIC_PATH)),
            array('edit/3', 'user', sprintf('href="%suser/edit/3"', PUBLIC_PATH)),
            array('', 'test', sprintf('href="%stest/"', PUBLIC_PATH)),
            array(null, 'test', sprintf('href="%stest/"', PUBLIC_PATH)),
        );
    }

    /**
     * @dataProvider linkActionDataProvider
     */
    public function testLinkActionHrefPattern($action, $controllerPath, $expected)
    {
        $routerMock = m::mock('alias:Router');
        $routerMock->shouldReceive('get')->with('controller_path')->andReturn($controllerPath);

        $tagMock = m::mock('alias:Tag');
        $tagMock->shouldReceive('getAttrs')->withAnyArgs()->andReturn('');

        $link = Html::linkAction($action, 'Link Text');

        $this->assertContains($expected, $link);
    }

    public function testLinkAction()
    {
        $routerMock = m::mock('alias:Router');
        $routerMock->shouldReceive('get')->with('controller_path')->andReturn('test');

        $tagMock = m::mock('alias:Tag');
        $tagMock->shouldReceive('getAttrs')->withAnyArgs()->andReturn('');

        $link = Html::linkAction('action-name', 'Link Text');

        $this->assertSame(
            '<a href="http://127.0.0.1/test/action-name"  >Link Text</a>',
            $link
        );
    }
}
