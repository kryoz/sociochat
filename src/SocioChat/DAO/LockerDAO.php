<?php

namespace SocioChat\DAO;

class LockerDAO extends DAOBase
{
	const KEY = 'uid';
	const TIMESTAMP = 'timestamp';

	public function __construct()
	{
		parent::__construct(
			[
				self::KEY,
				self::TIMESTAMP,
			]
		);

		$this->dbTable = 'locker';
	}

	public function getKey()
	{
		return $this[self::KEY];
	}

	public function getTimestamp()
	{
		return $this[self::TIMESTAMP];
	}

	public function getByKey($key)
	{
		$data = $this->getByPropId(self::KEY, $key);
		return $data->getId() ? $data : null;
	}

	public function setKey($key)
	{
		$this[self::KEY] = $key;
		return $this;
	}

	public function setTimestamp($time)
	{
		$this[self::TIMESTAMP] = $time;
		return $this;
	}

	protected function getForeignProperties()
	{
		return [];
	}
}

