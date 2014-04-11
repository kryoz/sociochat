<?php

namespace SocioChat\DAO;

class ActivationsDAO extends DAOBase
{
	const EMAIL = 'email';
	const CODE = 'code';
	const TIMESTAMP = 'timestamp';
	const USED = 'used';

	public function __construct()
	{
		parent::__construct(
			[
				self::EMAIL,
				self::CODE,
				self::TIMESTAMP,
				self::USED,
			]
		);

		$this->dbTable = 'activations';
	}

	public function getEmail()
	{
		return $this[self::EMAIL];
	}

	public function getTimestamp()
	{
		return $this[self::TIMESTAMP];
	}

	public function getCode()
	{
		return $this[self::CODE];
	}

	public function getIsUsed()
	{
		return $this[self::USED];
	}

	public function getByEmail($email)
	{
		return $this->getByPropId(self::EMAIL, $email);
	}

	public function setEmail($email)
	{
		$this[self::EMAIL] = $email;
		return $this;
	}

	public function setCode($code)
	{
		$this[self::CODE] = $code;
		return $this;
	}

	public function setTimestamp($time)
	{
		$this[self::TIMESTAMP] = $time;
		return $this;
	}

	public function setIsUsed($bool)
	{
		$this[self::USED] = $bool;
		return $this;
	}

	public function getActivation($email, $code)
	{
		return $this->getListByQuery("SELECT * FROM {$this->dbTable} WHERE email = :email AND code = :code LIMIT 1", ['email' => $email, 'code' => $code]);
	}

	protected function getForeignProperties()
	{
		return [];
	}
}

