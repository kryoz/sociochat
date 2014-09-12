<?php

namespace SocioChat\Response;

use SocioChat\Clients\User;
use Core\DI;
use SocioChat\Enum\SexEnum;
use SocioChat\Enum\TimEnum;

class UserPropetiesResponse extends Response
{
	protected $name = 'buddy';
	protected $email;
	protected $tim = TimEnum::FIRST;
	protected $sex = SexEnum::FIRST;
	protected $msg;
	protected $notifications;
	protected $avatarImg;
	protected $avatarThumb;
	protected $city;
	protected $birth;

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

	public function setAvatarImg($avatar)
	{
		$this->avatarImg = $avatar;
		return $this;
	}

	public function setAvatarThumb($avatarThumb)
	{
		$this->avatarThumb = $avatarThumb;
		return $this;
	}

	public function getCity()
	{
		return $this->city;
	}

	public function setCity($city)
	{
		$this->city = $city;
		return $this;
	}

	public function getYear()
	{
		return $this->birth;
	}

	public function setYear($year)
	{
		$this->birth = $year;
		return $this;
	}

	public function setUserProps(User $user)
	{
		$properties = $user->getProperties();
		$dir = DI::get()->getConfig()->uploads->avatars->wwwfolder.DIRECTORY_SEPARATOR;

		$this
			->setEmail($user->getUserDAO()->getEmail())
			->setSex($properties->getSex()->getId())
			->setTim($properties->getTim()->getId())
			->setName($properties->getName())
			->setNotifications($properties->getNotifications())
			->setAvatarImg($properties->getAvatarImg() ? $dir.$properties->getAvatarImg() : null)
			->setAvatarThumb($properties->getAvatarThumb() ? $dir.$properties->getAvatarThumb() : null)
			->setYear($properties->getBirthday())
			->setCity($properties->getCity());

		return $this;
	}
} 