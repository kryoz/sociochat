<?php

namespace SocioChat\DAO;

use Core\DAO\DAOBase;

class ReferralDAO extends DAOBase
{
    const USER_ID = 'user_id';
	const REF_USER_ID = 'ref_user_id';
	const DATE_REGISTER = 'date_register';

    public function __construct()
    {
        parent::__construct(
            [
                self::USER_ID,
	            self::REF_USER_ID,
	            self::DATE_REGISTER,
            ]
        );

        $this->dbTable = 'users_ref';
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

	public function getRefUserId()
	{
		return $this[self::REF_USER_ID];
	}

	public function setRefUserId($userId)
	{
		$this[self::REF_USER_ID] = $userId;
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

	/**
	 * @param $userId
	 * @param $guestId
	 * @return $this
	 */
	public function getByUserId($userId, $guestId)
    {
	    $list = $this->getListByQuery(
		    "SELECT * FROM {$this->dbTable} WHERE ".self::USER_ID ." = :0 AND ".self::REF_USER_ID." = :1 LIMIT 1",
		    [$userId, $guestId]
	    );

	    return count($list) ? $list[0] : null;
    }

	/**
	 * @param $userId
	 * @return $this
	 */
	public function getFirstRefByUserId($userId)
	{
		$list = $this->getListByQuery(
			"SELECT * FROM {$this->dbTable} WHERE ".self::USER_ID ." = :0 ORDER BY ".self::DATE_REGISTER." ASC LIMIT 1",
			[$userId]
		);

		return count($list) ? $list[0] : null;
	}

	public function dropByUserId($userId)
	{
		$this->db->exec("DELETE FROM {$this->dbTable} WHERE " . self::USER_ID . " = :id", ['id' => $userId]);
	}

    protected function getForeignProperties()
    {
        return [];
    }
}

