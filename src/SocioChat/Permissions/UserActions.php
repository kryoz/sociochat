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
	const KARMA_PLUS = 'karma-plus';
	const KARMA_MINUS = 'karma-minus';
	const KARMA = 'karma';

	protected $actions = [
		self::BAN => self::BAN,
		self::UNBAN => self::UNBAN,
		self::INVITE => self::INVITE,
		self::NOTE => self::NOTE,
		self::MAIL => self::MAIL,
		self::KARMA_PLUS => self::KARMA_PLUS,
		self::KARMA_MINUS => self::KARMA_MINUS,
		self::KARMA => self::KARMA,
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
			$actions = [self::KARMA => self::KARMA];
		} elseif (!$this->user->getEmail()) {
			unset($actions[self::KARMA_MINUS]);
			unset($actions[self::KARMA_PLUS]);
			unset($actions[self::MAIL]);
		} else {
			unset($actions[self::UNBAN]);
		}

		return array_keys($actions);
	}
}