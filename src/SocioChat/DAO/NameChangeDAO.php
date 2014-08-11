<?php

namespace SocioChat\DAO;

use SocioChat\Clients\User;

class NameChangeDAO extends DAOBase
{
	const USER_ID = 'user_id';
	const OLD_NAME = 'old_name';
	const DATE_CHANGE = 'date_change';

	public function __construct()
	{
		parent::__construct(
			[
				self::USER_ID,
				self::OLD_NAME,
				self::DATE_CHANGE,
			]
		);

		$this->dbTable = 'name_change_history';
	}

	public function getName()
	{
		return $this[self::OLD_NAME];
	}

	public function getDateRaw()
	{
		return $this[self::DATE_CHANGE];
	}

	public function getDate()
	{
		return strtotime($this[self::DATE_CHANGE]);
	}

	public function setUser(User $user)
	{
		$this[self::USER_ID] = $user->getId();
		$this[self::OLD_NAME] = $user->getProperties()->getName();
		$this[self::DATE_CHANGE] = date('Y-m-d H:i:s');
		return $this;
	}

	/**
	 * @param User $user
	 * @return $this
	 */
	public function getLastByUser(User $user)
	{
		$list = $this->getListByQuery("SELECT * FROM {$this->dbTable} WHERE user_id = :user_id ORDER BY ".self::DATE_CHANGE." DESC LIMIT 1", ['user_id' => $user->getId()]);
		return !empty($list) ? $list[0] : null;
	}

	public function getHistoryByUserId($userId)
	{
		return $this->getListByQuery("SELECT * FROM {$this->dbTable} WHERE user_id = :user_id ORDER BY ".self::DATE_CHANGE." DESC", ['user_id' => $userId]);
	}

	public function dropByUserId($id)
	{
		$this->db->exec("DELETE FROM {$this->dbTable} WHERE user_id = :0", [$id]);
	}

	protected function getForeignProperties()
	{
		return [];
	}
}

