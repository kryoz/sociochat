<?php

namespace SocioChat\DAO;

use SocioChat\Enum\SexEnum;
use SocioChat\Enum\TimEnum;

class PropertiesDAO extends DAOBase
{
	const USER_ID = 'user_id';
	const NAME = 'name';
	const SEX = 'sex';
	const TIM = 'tim';
	const NOTIFICATIONS = 'notifications';

	public function __construct()
	{
		parent::__construct(
			[
				self::USER_ID,
				self::NAME,
				self::SEX,
				self::TIM,
				self::NOTIFICATIONS,
			]
		);

		$this->dbTable = 'user_properties';
	}

	public function getByUserId($userId)
	{
		$this->setUserId($userId);
		return $this->getByPropId(self::USER_ID, $userId);
	}

	public function getByUserName($name)
	{
		$query = "SELECT * FROM {$this->dbTable} WHERE ".self::NAME." LIKE :name";
		if ($data = $this->db->query($query, ['name' => $name])) {
			$this->fillParams($data[0]);
		}

		return $this;
	}

	public function getName()
	{
		return $this[self::NAME];
	}

	public function setName($name)
	{
		$this[self::NAME] = $name;
		return $this;
	}

	public function getUserId()
	{
		return $this[self::USER_ID];
	}

	public function setUserId($id)
	{
		$this[self::USER_ID] = $id;
		return $this;
	}

	public function getSex()
	{
		return SexEnum::create($this[self::SEX]);
	}

	public function isFemale()
	{
		return $this[self::SEX] == SexEnum::FEMALE;
	}

	public function setSex(SexEnum $sex)
	{
		$this[self::SEX] = $sex->getId();
		return $this;
	}

	/**
	 * @return TimEnum
	 */
	public function getTim()
	{
		return TimEnum::create($this[self::TIM]);
	}

	public function setTim(TimEnum $tim)
	{
		$this[self::TIM] = $tim->getId();
		return $this;
	}

	public function getNotifications()
	{
		return json_decode($this[self::NOTIFICATIONS]);
	}

	public function setNotifications(array $settings)
	{
		$this[self::NOTIFICATIONS] = json_encode($settings);
		return $this;
	}

	protected function getForeignProperties()
	{
		return [];
	}

	public function toPublicArray()
	{
		return [
			self::USER_ID => $this->getUserId(),
			self::NAME => $this->getName(),
			self::TIM => $this->getTim()->getName(),
			self::SEX => $this->getSex()->getName(),
		];
	}

	public function importJSON($json)
	{
		$data = json_decode($json, 1);

		if ($data === null) {
			return;
		}

		$this->fillParams($data);
	}
}

