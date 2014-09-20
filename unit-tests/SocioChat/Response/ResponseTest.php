<?php
namespace Test\SocioChat\Response;

use PHPUnit_Framework_TestCase;
use SocioChat\Response\Response;
use stdClass;

class ResponseTest extends PHPUnit_Framework_TestCase
{
    public function testSetGetChatId()
    {
	    /** @var Response $response */
	    $response = $this->getMockForAbstractClass('SocioChat\\Response\\Response');
        $this->assertNull($response->getChannelId(), 'Default value is not NULL');
        $id = new stdClass;
        $this->assertEquals($response, $response->setChannelId($id), 'Fluent interface is not supported');
        $this->assertEquals($id, $response->getChannelId(), 'Getter returned a different value');

    }
}
