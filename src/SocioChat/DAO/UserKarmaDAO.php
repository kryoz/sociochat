<?php

namespace SocioChat\DAO;

use Core\DAO\DAOBase;
use SocioChat\Clients\User;
use SocioChat\DI;

class UserKarmaDAO extends DAOBase
{
    const USER_ID = 'user_id';
    const EVALUATOR_ID = 'evaluator_id';
    const MARK = 'mark';
	const DATE_REGISTER = 'date_register';

    public function __construct()
    {
        parent::__construct(
            [
                self::USER_ID,
                self::EVALUATOR_ID,
                self::MARK,
	            self::DATE_REGISTER,
            ]
        );

        $this->dbTable = 'user_karma';
    }

    public function getUserId()
    {
        return $this[self::USER_ID];
    }

    public function getEvaluatorId()
    {
        return $this[self::EVALUATOR_ID];
    }

    public function getMark()
    {
        return strtotime($this[self::MARK]);
    }

    public function setUserId($userId)
    {
        $this[self::USER_ID] = $userId;
        return $this;
    }

	public function setEvaluator(User $user)
	{
		$this[self::EVALUATOR_ID] = $user->getId();
		return $this;
	}

	public function setMark($mark)
	{
		$this[self::MARK] = (int) $mark;
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
	 * @param $evaluatorId
	 * @return UserKarmaDAO|null
	 */
	public function getLastMarkByEvaluatorId($userId, $evaluatorId)
	{
		$list = $this->getListByQuery(
			"SELECT * FROM {$this->dbTable} WHERE ".self::USER_ID ." = :0 AND ".self::EVALUATOR_ID." = :1 ORDER BY "
			.self::DATE_REGISTER." DESC LIMIT 1",
			[$userId, $evaluatorId]
		);

		return count($list) ? $list[0] : null;
	}

	/**
	 * @param $userId
	 * @return integer
	 */
	public function getKarmaByUserId($userId)
	{
		$mark = $this->db->query(
			"SELECT SUM(".self::MARK.") AS karma FROM {$this->dbTable} WHERE " . self::USER_ID . " = :0",
			[$userId]
		);

		return count($mark) ? $mark[0]['karma'] : 0;
	}

    protected function getForeignProperties()
    {
        return [];
    }
}

