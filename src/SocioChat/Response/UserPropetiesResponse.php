<?php

namespace SocioChat\Response;

use SocioChat\Clients\User;
use SocioChat\DI;
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
	protected $avatarImg = null;
	protected $avatarThumb = null;

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
			->setAvatarThumb($properties->getAvatarThumb() ? $dir.$properties->getAvatarThumb() : null);

		return $this;
	}
} 