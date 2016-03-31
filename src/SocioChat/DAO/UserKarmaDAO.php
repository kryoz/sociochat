<?php

namespace SocioChat\DAO;

use Core\BaseException;
use Core\DAO\DAOBase;
use JsonSerializable;
use SocioChat\Clients\User;
use SocioChat\DI;

class UserKarmaDAO extends DAOBase implements JsonSerializable
{
    const USER_ID = 'user_id';
    const EVALUATOR_ID = 'evaluator_id';
    const MARK = 'mark';
	const DATE_REGISTER = 'date_register';

    const EVALUATOR = 'evaluator';

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
        $this->addRelativeProperty(self::EVALUATOR);
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

	public function getMarksList($userId, $limit = 3)
	{
		$list = $this->getListByQuery(
			"SELECT t.*, t2.".PropertiesDAO::NAME." AS evaluator FROM {$this->dbTable} AS t
              JOIN user_properties AS t2 ON t2.".PropertiesDAO::USER_ID." = t.".self::EVALUATOR_ID."
              WHERE t.".self::USER_ID ." = :0 ORDER BY "
			.self::DATE_REGISTER." DESC LIMIT :1",
			[$userId, $limit]
		);

		return count($list) ? $list : null;
	}

    public function getEvaluator()
    {
        if (!$this[self::EVALUATOR] && $this->getId()) {
            $this[self::EVALUATOR] = PropertiesDAO::create()->getByUserId($this->getEvaluatorId());
        }

        if (!$this->getId()) {
            throw new BaseException('Incorrect DAO request, id is null' . debug_backtrace());
        }

        return $this[self::EVALUATOR];
    }

    protected function getForeignProperties()
    {
        return [self::EVALUATOR];
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        $props = $this->properties;
        unset($props[static::ID], $props[static::USER_ID], $props[static::EVALUATOR_ID]);
        return $props;
    }
}

