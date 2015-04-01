<?php

namespace SocioChat\Permissions;

use SocioChat\DAO\UserDAO;

class UserActions
{
	const BAN = 'ban';
	const UNBAN = 'unban';
	const INVITE = 'private';
	const NOTE = 'note';
	const MAIL = 'mail';

	protected $actions = [
		self::BAN => self::BAN,
		self::UNBAN => self::UNBAN,
		self::INVITE => self::INVITE,
		self::NOTE => self::NOTE,
		self::MAIL => self::MAIL,
	];

	/**
	 * @var UserDAO
	 */
	protected $user;

	public function __construct(UserDAO $user)
	{
		$this->user = $user;
	}

	public function getAllowed(UserDAO $guest)
	{
		$actions = $this->actions;

		if (!$guest->getEmail()) {
			unset($actions[self::MAIL]);
		}

		if ($this->user->getBlacklist()->isBanned($guest->getId())) {
			unset($actions[self::BAN]);
			unset($actions[self::INVITE]);
			unset($actions[self::MAIL]);
		} elseif ($guest->getId() == $this->user->getId()) {
			$actions = [];
		} else {
			unset($actions[self::UNBAN]);
		}

		return array_keys($actions);
	}
}