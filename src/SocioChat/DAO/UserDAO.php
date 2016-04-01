<?php

namespace SocioChat\DAO;

use Core\BaseException;
use Core\DAO\DAOBase;
use Core\Utils\DbQueryHelper;
use SocioChat\Enum\UserRoleEnum;

class UserDAO extends DAOBase
{
    const EMAIL = 'email';
    const PASSWORD = 'password';
    const DATE_REGISTER = 'date_register';
    const CHAT = 'chat_id';
    const ROLE = 'role';
    const IMPRINT = 'imprint';
    const IS_BANNED = 'is_banned';

    const PROPERTIES = 'properties';
    const BLACKLIST = 'blacklist';
    const NOTES = 'notes';

    protected $types = [
        self::IS_BANNED => \PDO::PARAM_BOOL,
    ];

    public function __construct()
    {
        parent::__construct(
            [
                self::EMAIL,
                self::PASSWORD,
                self::DATE_REGISTER,
                self::CHAT,
                self::ROLE,
                self::IMPRINT,
                self::IS_BANNED,
            ]
        );

        $this->dbTable = 'users';

        $this->addRelativeProperty(self::PROPERTIES);
        $this->addRelativeProperty(self::BLACKLIST);
        $this->addRelativeProperty(self::NOTES);
    }

    public function getEmail()
    {
        return $this[self::EMAIL];
    }

    public function setEmail($email)
    {
        $this[self::EMAIL] = $email;
        return $this;
    }

    public function getPassword()
    {
        return $this[self::PASSWORD];
    }

    public function setPassword($password)
    {
        $this[self::PASSWORD] = $password;
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

    public function getChatId()
    {
        return $this[self::CHAT];
    }

    public function setChatId($chatId)
    {
        $this[self::CHAT] = $chatId;
        return $this;
    }

    public function getByEmail($email)
    {
        return $this->getByPropId(self::EMAIL, $email);
    }

    public function setRole($role)
    {
        $this[self::ROLE] = $role;
        return $this;
    }

    /**
     * @return UserRoleEnum
     */
    public function getRole()
    {
        return UserRoleEnum::create($this[self::ROLE]);
    }


    public function getImprint()
    {
        return $this[self::IMPRINT];
    }

    public function setImprint($ip)
    {
        $this[self::IMPRINT] = $ip;
        return $this;
    }

    public function getByImprint($imprint)
    {
        return $this->getListByQuery("SELECT id FROM {$this->dbTable} WHERE " . self::IMPRINT . " = :imprint AND ".self::IS_BANNED.' IS TRUE LIMIT 1',
            ['imprint' => $imprint]
        );
    }

    public function isBanned()
    {
        return $this[self::IS_BANNED];
    }

    public function setBanned($ban)
    {
        $this[self::IS_BANNED] = (bool) $ban;
        return $this;
    }

    public function getUnregisteredUserIds()
    {
        return $this->db->query("SELECT id FROM {$this->dbTable} WHERE " . self::EMAIL . " IS NULL", [],
            \PDO::FETCH_COLUMN);
    }

    /**
     * @throws BaseException
     * @return PropertiesDAO
     */
    public function getPropeties()
    {
        if (!$this[self::PROPERTIES] && $this->getId()) {
            $this[self::PROPERTIES] = PropertiesDAO::create()->getByUserId($this->getId());
        }

        if (!$this->getId()) {
            throw new BaseException('Incorrect DAO request, id is null' . debug_backtrace());
        }

        return $this[self::PROPERTIES];
    }

	/**
	 * @return UserBlacklistDAO|null
	 */
    public function getBlacklist()
    {
        if (!$this[self::BLACKLIST] && $this->getId()) {
            $this[self::BLACKLIST] = UserBlacklistDAO::create()->getByUserId($this->getId());
        }

        return $this[self::BLACKLIST];
    }

	/**
	 * @return UserNotesDAO|null
	 */
    public function getUserNotes()
    {
        if (!$this[self::NOTES] && $this->getId()) {
            $this[self::NOTES] = UserNotesDAO::create()->getByUserId($this->getId());
        }

        return $this[self::NOTES];
    }

    public function dropByUserIdList(array $userIds)
    {
        $usersList = DbQueryHelper::commaSeparatedHolders($userIds);
        $this->db->exec("DELETE FROM {$this->dbTable} WHERE id IN ($usersList)", $userIds);
    }

    protected function getForeignProperties()
    {
        return [self::PROPERTIES, self::BLACKLIST, self::NOTES];
    }
}

