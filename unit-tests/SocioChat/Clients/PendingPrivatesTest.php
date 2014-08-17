<?php

namespace Test\SocioChat\Clients;

use SocioChat\Clients\PendingPrivates;
use Core\DI;
use Test\SocioChat\Helpers\MockEventLoop;
use Test\SocioChat\Helpers\TestSuite;

class PendingPrivatesTest extends TestSuite
{
    /**
     * @var PendingPrivates
     */
    protected $privates;

	protected function setUp()
	{
		parent::setUp();
		$this->privates = new PendingPrivates();
	}

	public function testGetToken()
	{
		$user1 = $this->getMockUser();
		$user2 = $this->getMockUser();

		$refl = self::getMethod(PendingPrivates::class, 'getToken');
		$token = $refl->invokeArgs($this->privates, [$user1, $user2]);

		$this->assertNotNull($token, 'token cant be null');
		$this->assertEquals($token, $refl->invokeArgs($this->privates, [$user2, $user1]), 'token must be equal');
	}

    public function testInvite()
    {
	    $user1 = $this->getMockUser();
	    $user2 = $this->getMockUser();

	    DI::get()->container()->add('eventloop', MockEventLoop::class, true);

        $dummy = function() {};

	    // send invitation
	    $inviteTimestamp = time();
        list($user1id, $time) = $this->privates->invite($user1, $user2, $dummy);

        $this->assertEquals($user1->getId(), $user1id);
	    $this->assertNull($time);

	    // repeat sending invitation, but it should return sign of 'already sent' by timestamp of first attempt
	    list($user1id, $time) = $this->privates->invite($user1, $user2, $dummy);

	    $this->assertEquals($user1->getId(), $user1id);
	    $this->assertEquals($inviteTimestamp, $time);

	    // accept invitation
	    list($user1id, $time) = $this->privates->invite($user2, $user1, $dummy);
	    $this->assertNull($user1id);
	    $this->assertNull($time);
    }
}
 