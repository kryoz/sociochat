<?php

namespace Test\SocioChat\Clients;

use SocioChat\Clients\PendingPrivates;
use SocioChat\Clients\User;
use Test\SocioChat\Helpers\TestSuite;

class PendingPrivatesTest extends TestSuite
{
	protected $privates;
	/**
	 * @var User
	 */
	protected $user1;
	/**
	 * @var User
	 */
	protected $user2;

	protected function setUp()
	{
		parent::setUp();
		$this->privates = new PendingPrivates();
	}

	public function testGetToken()
	{
		$user1 = $this->getMock(User::class, ['getId'], [], '', false);
		$user1->expects($this->any())->method('getId')->willReturn('1');

		$user2 = $this->getMock(User::class, ['getId'], [], '', false);
		$user2->expects($this->any())->method('getId')->willReturn('2');

		$refl = self::getMethod(PendingPrivates::class, 'getToken');

		$token = $refl->invokeArgs($this->privates, [$user1, $user2]);
		$this->assertNotNull($token, 'token cant be null');
		$this->assertEquals($token, $refl->invokeArgs($this->privates, [$user2, $user1]), 'token must be equal');
	}
}
 