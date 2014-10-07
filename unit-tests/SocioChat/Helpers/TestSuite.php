<?php

namespace Test\SocioChat\Helpers;

use PHPUnit_Framework_MockObject_MockObject;
use ReflectionClass;
use SocioChat\Clients\User;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\DI;
use SocioChat\DIBuilder;
use SocioChat\Enum\SexEnum;
use SocioChat\Enum\TimEnum;

class TestSuite extends \PHPUnit_Framework_TestCase
{
    protected $userSeq = 1;

    protected function setUp()
    {
        parent::setUpBeforeClass();
        $container = DI::get()->container();
        DIBuilder::setupConfig($container);
        DIBuilder::setupUsers($container);
        $container->add('db', null);
    }


    /**
     * @param int $timId
     * @param int $sexId
     * @return User|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockUser($timId = TimEnum::ILE, $sexId = SexEnum::MALE)
    {
        $user = $this->getMock(User::class, ['getId', 'getChannelId', 'getProperties'], [], '', false);
        $user->expects($this->any())->method('getId')->willReturn($this->userSeq);
        $user->expects($this->any())->method('getProperties')->willReturn($this->getMockProperties($timId, $sexId));
        $this->userSeq++;

        DI::get()->getUsers()->attach($user);

        return $user;
    }

    protected function getMockProperties($timId = TimEnum::ILE, $sexId = SexEnum::MALE)
    {
        $properties = $this->getMock(PropertiesDAO::class, ['getTim', 'getSex'], [], '', false);
        $properties->expects($this->any())->method('getTim')->willReturn(TimEnum::create($timId));
        $properties->expects($this->any())->method('getSex')->willReturn(SexEnum::create($sexId));
        return $properties;
    }

    protected static function getMethod($class, $methodName)
    {
        $class = new ReflectionClass($class);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }
} 