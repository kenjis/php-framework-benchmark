<?php

namespace AppTest\Action;

use App\Action\PingAction;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class PingActionTest extends \PHPUnit_Framework_TestCase
{
    public function testResponse()
    {
        $pingAction = new PingAction();
        $response = $pingAction(new ServerRequest(['/']), new Response(), function () {
        });
        $json = json_decode((string) $response->getBody());

        $this->assertTrue($response instanceof Response);
        $this->assertTrue($response instanceof Response\JsonResponse);
        $this->assertTrue(isset($json->ack));
    }
}
