<?php

namespace SocioChat\DAO;

use Core\DAO\DAOBase;

class TmpSessionDAO extends DAOBase
{
    const SESSION_ID = 'session_id';

    public function __construct()
    {
        parent::__construct(
            [
                self::SESSION_ID,
            ]
        );

        $this->dbTable = 'tmp_sessions';
    }

    public function getSessionId()
    {
        return $this[self::SESSION_ID];
    }


    public function getBySessionId($id)
    {
        return $this->getByPropId(self::SESSION_ID, $id);
    }

    public function setSessionId($sessionId)
    {
        $this[self::SESSION_ID] = $sessionId;
        return $this;
    }

	public function dropAll()
	{
		$this->db->query("DELETE FROM {$this->dbTable}");
	}

    protected function getForeignProperties()
    {
        return [];
    }
}

