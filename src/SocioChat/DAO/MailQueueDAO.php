<?php

namespace SocioChat\DAO;

use Core\DAO\DAOBase;
use Core\Utils\DbQueryHelper;

class MailQueueDAO extends DAOBase
{
    const EMAIL = 'email';
	const TOPIC = 'topic';
	const MESSAGE = 'message';
    const DATE = 'date_register';


    public function __construct()
    {
        parent::__construct(
            [
                self::EMAIL,
	            self::TOPIC,
	            self::MESSAGE,
                self::DATE,
            ]
        );

        $this->dbTable = 'mail_queue';
	    $this->setDate(DbQueryHelper::timestamp2date());
    }

    public function getEmail()
    {
        return $this[self::EMAIL];
    }

	public function getTopic()
	{
		return $this[self::TOPIC];
	}

	public function getMessage()
	{
		return $this[self::MESSAGE];
	}

    public function getDate()
    {
        return $this[self::DATE];
    }

    public function getByEmail($id)
    {
        return $this->getByPropId(self::EMAIL, $id);
    }

    public function setEmail($email)
    {
        $this[self::EMAIL] = $email;
        return $this;
    }

    public function setDate($time)
    {
        $this[self::DATE] = $time;
        return $this;
    }

	public function setTopic($topic)
	{
		$this[self::TOPIC] = $topic;
		return $this;
	}

    public function setMessage($message)
    {
        $this[self::MESSAGE] = $message;
        return $this;
    }

    protected function getForeignProperties()
    {
        return [];
    }
}

