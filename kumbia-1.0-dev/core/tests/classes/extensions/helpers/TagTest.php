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

/**
 * @category Test
 */
class TagTest extends PHPUnit_Framework_TestCase
{
    public function jsFileProvider()
    {
        return array(
            array('file'),
            array('path/to/file2'),
            array('path/to/file-3'),
        );
    }

    /**
     * @dataProvider jsFileProvider
     */
    public function testJs($file)
    {
        $scriptPattern = '<script type="text/javascript" src="%sjavascript/%s"></script>';
        $response = Tag::js($file);
        $expected = sprintf($scriptPattern, PUBLIC_PATH, $file.'.js');

        $this->assertSame($expected, $response);
    }

    /**
     * @dataProvider jsFileProvider
     */
    public function testJsNoCache($file)
    {
        $scriptPattern = '<script type="text/javascript" src="%sjavascript/%s?nocache=';

        $response = Tag::js($file, false);
        $expected = sprintf($scriptPattern, PUBLIC_PATH, $file.'.js');

        $this->assertStringStartsWith($expected, $response);
        $this->assertStringEndsWith('"></script>', $response);
    }

    public function testGetAttrsPassingArray()
    {
        $response = Tag::getAttrs(array(
            'attr-one' => 'value-one',
            'attr-two' => 'value-two',
        ));

        $expected = ' attr-one="value-one" attr-two="value-two"';
        $this->assertSame($expected, $response);
    }

    public function testGetAttrsPassingString()
    {
        $expected = 'attr-one="value-one" attr-two="value-two"';
        $response = Tag::getAttrs($expected);

        $this->assertSame($expected, $response);
    }

    public function testAddAndGetCssFiles()
    {
        $this->assertEmpty(Tag::getCss());

        Tag::css('css1');
        Tag::css('css2', 'print');
        Tag::css('css3');

        $files = Tag::getCss();
        $this->assertCount(3, $files);

        $this->assertInternalCssValue('css1', 'screen', $files[0]);
        $this->assertInternalCssValue('css2', 'print', $files[1]);
        $this->assertInternalCssValue('css3', 'screen', $files[2]);
    }

    public function createTagDataProvider()
    {
        return array(
            array(
                'a',
                array('href' => PUBLIC_PATH, 'class' => 'btn'),
                null,
                sprintf('<a  href="%s" class="btn" />', PUBLIC_PATH)
            ),
            array(
                'input',
                array('type' => 'text', 'value' => 'Hola KumbiaPHP'),
                null,
                '<input  type="text" value="Hola KumbiaPHP" />'
            ),
            array(
                'input',
                'value="Hola KumbiaPHP" type="text"',
                null,
                '<input value="Hola KumbiaPHP" type="text" />'
            ),
            array(
                'script',
                array('type' => 'text/javascript'),
                'console.log("Hola KumbiaPHP");',
                '<script  type="text/javascript">console.log("Hola KumbiaPHP");</script>',
            ),
        );
    }

    /**
     * @dataProvider createTagDataProvider
     */
    public function testCreateWithoutContent($tag, $attrs, $content, $expectedResult)
    {
        $this->markTestSkipped("Se debe corregir el método Tag::create para que el test corra");
        // TODO: el método tiene un if, pero igual el proceso despues del if se ejecuta.
        ob_start();
        Tag::create($tag, $content, $attrs);
        $html = ob_get_clean();

        $this->assertSame($expectedResult, $html);
    }

    /**
     * @param $file
     * @param $media
     * @param $data
     */
    private function assertInternalCssValue($file, $media, $data)
    {
        $this->assertArrayHasKey('src', $data);
        $this->assertArrayHasKey('media', $data);
        $this->assertSame($file, $data['src']);
        $this->assertSame($media, $data['media']);
    }
}
