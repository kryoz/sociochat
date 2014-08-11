<?php

namespace SocioChat\DAO;

use SocioChat\Utils\DbQueryHelper;

class UserBlacklistDAO extends DAOBase
{
	const IGNORED_ID = 'ignored_user_id';
	const USER_ID = 'user_id';

	protected $blacklist = [];

	public function __construct()
	{
		parent::__construct(
			[
				self::USER_ID,
				self::IGNORED_ID,
			]
		);

		$this->dbTable = 'user_blacklist';
	}

	public function getIgnoredList()
	{
		return $this->blacklist;
	}

	public function getByUserId($userId)
	{
		$list = $this->db->query("SELECT ignored_user_id FROM {$this->dbTable} WHERE user_id = :0", [$userId]);
		$this->blacklist = array_flip(array_column($list, 'ignored_user_id'));

		$this[self::USER_ID] = $userId;

		return $this;
	}

	public function isBanned($userId)
	{
		return isset($this->blacklist[$userId]);
	}

	public function banUserId($userId)
	{
		if (!isset($this->blacklist[$userId]) && $userId != $this->getUserId()) {
			$this->blacklist[$userId] = 1;
			return true;
		}
	}

	public function unbanUserId($userId)
	{
		unset($this->blacklist[$userId]);
	}

	public function getUserId()
	{
		return $this[self::USER_ID];
	}

	public function dropByUserId($id)
	{
		$this->db->exec("DELETE FROM {$this->dbTable} WHERE user_id = :0 OR ignored_user_id = :1", [$id, $id]);
	}

	public function dropByUserIdList(array $userIds)
	{
		$usersList = DbQueryHelper::commaSeparatedHolders($userIds);
		$this->db->exec("DELETE FROM {$this->dbTable} WHERE id IN ($usersList)", $userIds);
	}

	public function save($sequence = null)
	{
		$this->db->exec("DELETE FROM {$this->dbTable} WHERE ".self::USER_ID." = :0", [$this->getUserId()]);

		if (empty($this->blacklist)) {
			return;
		}

		$list = [];
		foreach ($this->blacklist as $bannedId => $v) {
			$list[] = '('.$this->getUserId().', '.$bannedId.')';
		}

		$this->db->exec("INSERT INTO {$this->dbTable} (".self::USER_ID.", ".self::IGNORED_ID.") VALUES ".implode(', ', $list));
	}

	protected function getForeignProperties()
	{
		return [];
	}
}

