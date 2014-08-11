<?php

namespace SocioChat\DAO;

use SocioChat\DI;
use SocioChat\Utils\DbQueryHelper;

class UserDAO extends DAOBase
{
	const SOCIAL_TOKEN = 'social_token';
	const EMAIL = 'email';
	const PASSWORD = 'password';
	const DATE_REGISTER = 'date_register';
	const CHAT = 'chat_id';

	const PROPERTIES = 'properties';
	const BLACKLIST = 'blacklist';

	public function __construct()
	{
		parent::__construct(
			[
				self::SOCIAL_TOKEN,
				self::EMAIL,
				self::PASSWORD,
				self::DATE_REGISTER,
				self::CHAT,
			]
		);

		$this->dbTable = 'users';

		$this->addRelativeProperty(self::PROPERTIES);
		$this->addRelativeProperty(self::BLACKLIST);
	}

	public function getToken()
	{
		return $this[self::SOCIAL_TOKEN];
	}

	public function setToken($token)
	{
		$this[self::SOCIAL_TOKEN] = $token;
		return $this;
	}

	public function getEmail()
	{
		return $this[self::EMAIL];
	}

	public function setEmail($email)
	{
		$this[self::EMAIL] = $email;
		return $this;
	}

	public function getPassword()
	{
		return $this[self::PASSWORD];
	}

	public function setPassword($password)
	{
		$this[self::PASSWORD] = $password;
		return $this;
	}

	public function getDateRegister()
	{
		return $this[self::DATE_REGISTER];
	}

	public function setDateRegister($date)
	{
		$this[self::DATE_REGISTER] = $date;
		return $this;
	}

	public function getChatId()
	{
		return $this[self::CHAT];
	}

	public function setChatId($chatId)
	{
		$this[self::CHAT] = $chatId;
		return $this;
	}

	public function getByEmail($email)
	{
		return $this->getByPropId(self::EMAIL, $email);
	}

	public function getUnregisteredUserIds()
	{
		return $this->db->query("SELECT id FROM {$this->dbTable} WHERE email IS NULL", [], \PDO::FETCH_COLUMN);
	}

	/**
	 * @throws \Exception
	 * @return PropertiesDAO
	 */
	public function getPropeties()
	{
		if (!$this[self::PROPERTIES] && $this->getId()) {
			$this[self::PROPERTIES] = PropertiesDAO::create()->getByUserId($this->getId());
		}

		if (!$this->getId()) {
			throw new \Exception('Incorrect DAO request, id is null'.debug_print_backtrace());
		}

		return $this[self::PROPERTIES];
	}

	public function getBlacklist()
	{
		if (!$this[self::BLACKLIST] && $this->getId()) {
			$this[self::BLACKLIST] = UserBlacklistDAO::create()->getByUserId($this->getId());
		}

		return $this[self::BLACKLIST];
	}

	public function dropByUserIdList(array $userIds)
	{
		$usersList = DbQueryHelper::commaSeparatedHolders($userIds);
		$this->db->exec("DELETE FROM {$this->dbTable} WHERE id IN ($usersList)", $userIds);
	}

	protected function getForeignProperties()
	{
		return [self::PROPERTIES, self::BLACKLIST];
	}
}

