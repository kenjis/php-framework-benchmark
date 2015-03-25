<?php

namespace My\Hello\Resource\Page;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BEAR\Resource\ResourceInterface
     */
    private $resource;

    protected function setUp()
    {
        parent::setUp();
        $this->resource = clone $GLOBALS['RESOURCE'];
    }

    public function testOnGet()
    {
        // resource request
        $page = $this->resource->get->uri('page://self/index')->withQuery(['name' => 'koriym'])->eager->request();
        $this->assertSame(200, $page->code);
        $this->assertSame('Hello koriym', $page['greeting']);

        return $page;
    }

    /**
     * @depends testOnGet
     */
    public function testView($page)
    {
        $json = json_decode((string) $page);
        $this->assertSame('Hello koriym', $json->greeting);
    }
}
