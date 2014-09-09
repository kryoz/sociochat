<?php

namespace SocioChat\DAO;

use Core\DAO\DAOBase;

class ActivationsDAO extends DAOBase
{
	const EMAIL = 'email';
	const CODE = 'code';
	const TIMESTAMP = 'timestamp';
	const USED = 'used';

	protected $types = [
		self::USED => \PDO::PARAM_BOOL,
	];

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
		return $this->getListByQuery("SELECT * FROM {$this->dbTable} WHERE email = :email AND code = :code AND used = :used LIMIT 1", ['email' => $email, 'code' => $code, 'used' => 'false']);
	}

	public function dropUsedActivations()
	{
		return $this->db->exec("DELETE FROM {$this->dbTable} WHERE used = :used", ['used' => 'true']);
	}

	protected function getForeignProperties()
	{
		return [];
	}
}

