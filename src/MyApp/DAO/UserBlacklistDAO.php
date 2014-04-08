<?php

namespace MyApp\DAO;

use MyApp\Log;

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
		$list = $this->db->query("SELECT ignored_user_id FROM {$this->dbTable} WHERE user_id = ?", [$userId]);
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
			Log::get()->fetch()->info("added userId = $userId to ban", [__CLASS__]);
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

	public function save($sequence = null)
	{
		$this->db->exec("DELETE FROM {$this->dbTable} WHERE ".self::USER_ID." = ?", [$this->getUserId()]);

		$list = [];
		foreach ($this->blacklist as $bannedId => $v) {
			$list[] = '('.$this->getUserId().', '.$bannedId.')';
		}

		if (!empty($this->blacklist)) {
			$this->db->exec("INSERT INTO {$this->dbTable} (".self::USER_ID.", ".self::IGNORED_ID.") VALUES ".implode(', ', $list));
		}
	}

	protected function getForeignProperties()
	{
		return [];
	}
}

