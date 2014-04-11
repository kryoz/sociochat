<?php
namespace Test\SocioChat\Response;

use PHPUnit_Framework_TestCase;
use stdClass;

class ResponseTest extends PHPUnit_Framework_TestCase
{
    public function testSetGetChatId()
    {
        $response = $this->getMockForAbstractClass('SocioChat\\Response\\Response');
        $this->assertNull($response->getChatId(), 'Default value is not NULL');
        $id = new stdClass;
        $this->assertEquals($response, $response->setChatId($id), 'Fluent interface is not supported');
        $this->assertEquals($id, $response->getChatId(), 'Getter returned a different value');

    }
}
