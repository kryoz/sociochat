<?php

namespace SocioChat\DAO;

use Core\DAO\DAOBase;
use Core\Utils\DbQueryHelper;
use SocioChat\Clients\User;

class HashDAO extends DAOBase
{
    const NAME = 'username';
    const DATE = 'date';
    const MESSAGE = 'message';
    const TOTAL_COUNT = 'total_count';

    private $total;

    public function __construct()
    {
        parent::__construct(
            [
                self::NAME,
                self::DATE,
                self::MESSAGE,
            ]
        );

        $this->dbTable = 'hashes';
    }

    public function getName()
    {
        return $this[self::NAME];
    }

    public function getDateRaw()
    {
        return $this[self::DATE];
    }

    public function getDate()
    {
        return strtotime($this[self::DATE]);
    }

    public function getMessage()
    {
        return $this[self::MESSAGE];
    }

    public function getTotalCount()
    {
        return $this->total;
    }

    public function setUser(User $user)
    {
        $this[self::NAME] = $user->getProperties()->getName();
        return $this;
    }

    public function setMessage($msg)
    {
        $this[self::MESSAGE] = $msg;
        return $this;
    }

    public function setDate($date)
    {
        $this[self::DATE] = $date;
        return $this;
    }

    public function setTotalCount($count)
    {
        $this->total = $count;
        return $this;
    }

    /**
     * @param string $name
     * @param int $offset
     * @param int $limit
     * @return $this
     */
    public function getListByName($name, $offset = 0, $limit = 10)
    {
        $list = $this->getListByQuery(
            "SELECT * FROM {$this->dbTable} WHERE " . self::NAME . " = :name ORDER BY " . self::DATE . " DESC LIMIT :limit OFFSET :start",
            [
                'name' => $name,
                'limit' => $limit,
                'start' => $offset,
            ]
        );
        return !empty($list) ? $list[0] : null;
    }

    /**
     * @param $hash
     * @param int $offset
     * @param int $limit
     * @return $this[]
     */
    public function getListByHash($hash, $offset = 0, $limit = 10)
    {
        $result = [];
        $query = "SELECT *, count(id) OVER() AS ".self::TOTAL_COUNT." FROM {$this->dbTable} WHERE "
            . "hashes_tsv @@ plainto_tsquery(:hash) ORDER BY "
            . self::DATE . " DESC LIMIT :limit OFFSET :start";

        $params = [
            'hash' => $hash,
            'start' => $offset,
            'limit' => $limit,
        ];

        foreach ($this->db->query($query, $params) as $item) {
            $entity = static::create();

            foreach ($item as $property => $value) {
                $entity[$property] = $value;
            }
            $entity->setTotalCount($item[self::TOTAL_COUNT]);
            $result[] = $entity;
        }

        return $result;
    }

    protected function getForeignProperties()
    {
        return [];
    }
}
