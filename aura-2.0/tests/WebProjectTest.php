<?php
namespace Aura\Web_Project;

class WebProjectTest extends \PHPUnit_Framework_TestCase
{
    public function testWeb()
    {
        $url = "http://localhost:8080/index.php";
        $actual = file_get_contents($url);
        $expect = 'Hello World!';
        $this->assertSame($expect, $actual);
    }
}
