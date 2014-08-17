<?php

namespace Test\SocioChat\Helpers;

use PHPUnit_Framework_MockObject_MockObject;
use ReflectionClass;
use SocioChat\Clients\User;
use SocioChat\DAO\PropertiesDAO;
use Core\DI;
use SocioChat\DIBuilder;
use SocioChat\Enum\TimEnum;

class TestSuite extends \PHPUnit_Framework_TestCase
{
	protected $userSeq = 1;

	protected function setUp()
	{
		parent::setUpBeforeClass();
		$container = DI::get()->container();
		DIBuilder::setupConfig($container);
		$container->add('db', null);
	}


	/**
	 * @param int $timId
	 * @return User|PHPUnit_Framework_MockObject_MockObject
	 */
	protected function getMockUser($timId = TimEnum::ILE)
	{
		$user = $this->getMock(User::class, ['getId', 'getChatId', 'getProperties'], [], '', false);
		$user->expects($this->any())->method('getId')->willReturn($this->userSeq);
		$user->expects($this->any())->method('getProperties')->willReturn($this->getMockProperties($timId));
		$this->userSeq++;

		return $user;
	}

	protected function getMockProperties($timId = TimEnum::ILE)
	{
		$properties = $this->getMock(PropertiesDAO::class, ['getTim'], [], '', false);
		$properties->expects($this->any())->method('getTim')->willReturn(TimEnum::create($timId));
		return $properties;
	}

	protected static function getMethod($class, $methodName) {
		$class = new ReflectionClass($class);
		$method = $class->getMethod($methodName);
		$method->setAccessible(true);
		return $method;
	}
} 