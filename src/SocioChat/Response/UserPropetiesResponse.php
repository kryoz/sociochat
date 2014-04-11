<?php

namespace SocioChat\Response;

use SocioChat\Clients\User;
use SocioChat\Enum\SexEnum;
use SocioChat\Enum\TimEnum;

class UserPropetiesResponse extends Response
{
	protected $name = 'buddy';
	protected $email = null;
	protected $tim = TimEnum::FIRST;
	protected $sex = SexEnum::FIRST;
	protected $msg = null;
	protected $notifications = null;

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function setTim($tim)
	{
		$this->tim = $tim;
		return $this;
	}

	public function setSex($sex)
	{
		$this->sex = $sex;
		return $this;
	}

	public function setMsg($msg)
	{
		$this->msg = $msg;
		return $this;
	}

	public function setNotifications($notifications)
	{
		$this->notifications = $notifications;
		return $this;
	}

	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	public function setUserProps(User $user)
	{
		$properties = $user->getProperties();

		$this->setEmail($user->getEmail());
		$this->setSex($properties->getSex()->getId());
		$this->setTim($properties->getTim()->getId());
		$this->setName($properties->getName());
		$this->setNotifications($properties->getNotifications());
		return $this;
	}
} 