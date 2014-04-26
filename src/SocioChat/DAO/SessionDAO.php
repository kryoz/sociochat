<?php

namespace SocioChat\DAO;

use PDO;
use SocioChat\Utils\DbQueryHelper;

class SessionDAO extends DAOBase
{
	const SESSION_ID = 'session_id';
	const ACCESS_TIME = 'access';
	const USER_ID = 'user_id';

	public function __construct()
	{
		parent::__construct(
			[
				self::SESSION_ID,
				self::ACCESS_TIME,
				self::USER_ID,
			]
		);

		$this->dbTable = 'sessions';
	}

	public function getSessionId()
	{
		return $this[self::SESSION_ID];
	}

	public function getAccessTime()
	{
		return $this[self::ACCESS_TIME];
	}

	public function getBySessionId($id)
	{
		return $this->getByPropId(self::SESSION_ID, $id);
	}

	public function getByUserId($userId)
	{
		return $this->getByPropId(self::USER_ID, $userId);
	}

	public function setSessionId($sessionId)
	{
		$this[self::SESSION_ID] = $sessionId;
		return $this;
	}

	public function setAccessTime($time)
	{
		$this[self::ACCESS_TIME] = $time;
		return $this;
	}

	public function setUserId($userId)
	{
		$this[self::USER_ID] = $userId;
		return $this;
	}

	public function getUserId()
	{
		return $this[self::USER_ID];
	}

	public function getObsoleteUserIds($deadLine)
	{
		if (!$unregisteredList = UserDAO::create()->getUnregisteredUserIds()) {
			return [];
		}

		$queryParams = array_merge([$deadLine], $unregisteredList);

		return $this->db->query(
			"SELECT user_id FROM {$this->dbTable} WHERE access < ? AND user_id IN (".DbQueryHelper::commaSeparatedHolders($unregisteredList).")",
			$queryParams,
			PDO::FETCH_COLUMN
		);
	}

	public function dropByUserId($userId)
	{
		$this->db->exec("DELETE FROM {$this->dbTable} WHERE user_id = ?", [$userId]);
	}

	public function dropByUserIdList(array $userIds)
	{
		$usersList = DbQueryHelper::commaSeparatedHolders($userIds);
		$this->db->exec("DELETE FROM {$this->dbTable} WHERE user_id IN ($usersList)", $userIds);
	}

	protected function getForeignProperties()
	{
		return [];
	}
}

