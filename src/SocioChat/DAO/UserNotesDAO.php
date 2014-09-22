<?php

namespace SocioChat\DAO;

use Core\DAO\DAOBase;
use Core\Utils\DbQueryHelper;

class UserNotesDAO extends DAOBase
{
	const NOTED_ID = 'noted_user_id';
	const USER_ID = 'user_id';
	const NOTE = 'note';

	private $notes;

	public function __construct()
	{
		parent::__construct(
			[
				self::USER_ID,
				self::NOTED_ID,
				self::NOTE,
			]
		);

		$this->dbTable = 'user_notes';
	}

	public function getByUserId($userId)
	{
		$list = $this->db->query("SELECT ".self::NOTED_ID.", ".self::NOTE." FROM {$this->dbTable} WHERE ".self::USER_ID." = :0", [$userId]);
		$keys = array_column($list, self::NOTED_ID);
		$vals = array_column($list, self::NOTE);
		$this->notes = array_combine($keys, $vals);

		$this[self::USER_ID] = $userId;

		return $this;
	}

	public function getUserId()
	{
		return $this[self::USER_ID];
	}

	public function setUserId($id)
	{
		$this[self::USER_ID] = $id;
		return $this;
	}

	public function getNotedUserId()
	{
		return $this[self::NOTED_ID];
	}

	public function setNotedUserId($id)
	{
		$this[self::NOTED_ID] = $id;
		return $this;
	}

	public function getNote($userId)
	{
		return $this->hasNote($userId) ? $this->notes[$userId] : null;
	}

	public function setNote($note)
	{
		$this[self::NOTE] = $note;

		if ($note) {
			$this->notes[$this->getNotedUserId()] = $note;
		} else {
			unset($this->notes[$this->getNotedUserId()]);
		}

		return $this;
	}

	public function hasNote($userId)
	{
		return isset($this->notes[$userId]);
	}

	public function dropByUserId($id)
	{
		$this->db->exec("DELETE FROM {$this->dbTable} WHERE ".self::USER_ID." = :0 OR ".self::NOTED_ID." = :1", [$id, $id]);
	}

	public function dropByUserIdList(array $userIds)
	{
		$usersList = DbQueryHelper::commaSeparatedHolders($userIds);
		$this->db->exec("DELETE FROM {$this->dbTable} WHERE ".self::USER_ID." IN ($usersList)", $userIds);
	}

	public function save($sequence = null)
	{
		$this->db->exec("DELETE FROM {$this->dbTable} WHERE ".self::USER_ID." = :0", [$this->getUserId()]);

		if (empty($this->notes)) {
			return;
		}

		$list = [];
		foreach ($this->notes as $notedUserId => $note) {
			$list[] = '('.$this->getUserId().', '.$notedUserId.', '.$this->db->o()->quote($note).')';
		}

		$this->db->exec("INSERT INTO {$this->dbTable} (".self::USER_ID.", ".self::NOTED_ID.", ".self::NOTE.") VALUES ".implode(', ', $list));
	}

	protected function getForeignProperties()
	{
		return [];
	}
}

