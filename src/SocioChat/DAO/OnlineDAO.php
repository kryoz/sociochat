<?php

namespace SocioChat\DAO;

use Core\DAO\DAOBase;

class OnlineDAO extends DAOBase
{
    const USER_ID = 'user_id';

    public function __construct()
    {
        parent::__construct(
            [
                self::USER_ID,
            ]
        );

        $this->dbTable = 'users_online';
    }

    public function getUserId()
    {
        return $this[self::USER_ID];
    }

	public function setUserId($userId)
	{
		$this[self::USER_ID] = $userId;
		return $this;
	}

    public function getByUserId($id)
    {
        return $this->getByPropId(self::USER_ID, $id);
    }

	public function dropByUserId($userId)
	{
		$this->db->exec("DELETE FROM {$this->dbTable} WHERE " . self::USER_ID . " = :id", ['id' => $userId]);
	}

	public function updateUserId($oldUserId, $newUserId)
	{
		$this->db->exec(
			"UPDATE {$this->dbTable} SET ".self::USER_ID." = :newUserId WHERE "
			. self::USER_ID . " = :oldUserId",
			[
				'oldUserId' => $oldUserId,
				'newUserId' => $newUserId,
			]
		);
	}

	/**
	 * @return integer
	 */
	public function getOnlineCount()
	{
		$mark = $this->db->query("SELECT count(*) AS online FROM {$this->dbTable} ");

		return count($mark) ? $mark[0]['online'] : 0;
	}

    protected function getForeignProperties()
    {
        return [];
    }
}

