<?php

namespace Test\SocioChat\Clients;

use SocioChat\Clients\PendingDuals;
use SocioChat\Enum\TimEnum;
use Test\SocioChat\Helpers\TestSuite;

class PendingDualsTest extends TestSuite
{
    /**
     * @var PendingDuals
     */
    protected $duals;

	protected function setUp()
	{
		parent::setUp();
		$this->duals = new PendingDuals();
	}

    public function testEnqueue()
    {
	    $user = $this->getMockUser();
	    $user->expects($this->any())->method('getChatId')->willReturn(1);

	    $this->assertFalse($this->duals->getUserPosition($user), 'user has not been enqueued yet');
		$this->assertFalse($this->duals->matchDual($user), 'userId is returned only when there is a match');
	    $this->assertEquals(1, $this->duals->getUserPosition($user), 'the first queue position must be 1');

	    $user = $this->getMockUser();
	    $user->expects($this->any())->method('getChatId')->willReturn(1);

	    $this->duals->matchDual($user);
	    $this->assertEquals(2, $this->duals->getUserPosition($user), 'queue counter has failed');

	    $user = $this->getMockUser(TimEnum::EIE);
	    $user->expects($this->any())->method('getChatId')->willReturn(1);

	    $this->duals->matchDual($user);
	    $this->assertEquals(1, $this->duals->getUserPosition($user), 'each TIM has its own queue');
    }

	public function testMatch()
	{
		$user1 = $this->getMockUser(TimEnum::ILE);
		$user1->expects($this->any())->method('getChatId')->willReturn(1);
		$user2 = $this->getMockUser(TimEnum::SEI);
		$user2->expects($this->any())->method('getChatId')->willReturn(1);

		$this->duals->matchDual($user1);

		$this->assertEquals($user1->getId(), $this->duals->matchDual($user2), 'matchDual must return matcher userId');
	}

	public function testInPrivate()
	{
		$user = $this->getMockUser();
		$user->expects($this->any())->method('getChatId')->willReturn('_qwerty123');

		$this->assertFalse($this->duals->matchDual($user), 'user cannot call dual mode in private chat');
		$this->assertFalse($this->duals->getUserPosition($user), 'user cannot call dual mode in private chat');
	}
}
 