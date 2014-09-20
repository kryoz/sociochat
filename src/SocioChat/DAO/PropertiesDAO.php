<?php

namespace SocioChat\DAO;

use Core\DAO\DAOBase;
use SocioChat\DI;
use SocioChat\Enum\SexEnum;
use SocioChat\Enum\TimEnum;
use Core\Utils\DbQueryHelper;
use SocioChat\Forms\Rules;

class PropertiesDAO extends DAOBase
{
	const USER_ID = 'user_id';
	const NAME = 'name';
	const SEX = 'sex';
	const TIM = 'tim';
	const NOTIFICATIONS = 'notifications';
	const AVATAR = 'avatar';
	const CITY = 'city';
	const BIRTH = 'birth';
    const CENSOR = 'censor';

	public function __construct()
	{
		parent::__construct(
			[
				self::USER_ID,
				self::NAME,
				self::SEX,
				self::TIM,
				self::NOTIFICATIONS,
				self::AVATAR,
				self::CITY,
				self::BIRTH,
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
		if ($data = $this->db->query($query, [self::NAME => $name])) {
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

	public function getAvatarImg()
	{
		return $this[self::AVATAR] ? $this[self::AVATAR].'.jpg' : null;
	}

	public function getAvatarThumb()
	{
		return $this[self::AVATAR] ? $this[self::AVATAR].'_t.png' : null;
	}

	public function getAvatarThumb2X()
	{
		return $this[self::AVATAR] ? $this[self::AVATAR].'_t@2x.png' : null;
	}

	public function setAvatarImg($img)
	{
		$this[self::AVATAR] = $img;
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

	public function getOptions()
	{
		return json_decode($this[self::NOTIFICATIONS], 1);
	}

	public function setOptions(array $settings)
	{
		$this[self::NOTIFICATIONS] = json_encode($settings);
		return $this;
	}

	public function getCity()
	{
		return $this[self::CITY];
	}

	public function setCity($city)
	{
		$this[self::CITY] = $city;
		return $this;
	}

	public function getBirthday()
	{
		return $this[self::BIRTH] ? date('Y', strtotime($this[self::BIRTH])) : Rules::LOWEST_YEAR;
	}

	public function setBirthday($year)
	{
		$this[self::BIRTH] = date("$year-01-01 12:00:00");
		return $this;
	}

    public function hasCensor()
    {
        return isset($this->getOptions()[self::CENSOR]) ? $this->getOptions()[self::CENSOR] : false;
    }

	public function dropByUserId($id)
	{
		$this->dropById($id, 'user_id');
	}

	public function dropByUserIdList(array $userIds)
	{
		$usersList = DbQueryHelper::commaSeparatedHolders($userIds);
		$this->db->exec("DELETE FROM {$this->dbTable} WHERE ".self::USER_ID." IN ($usersList)", $userIds);
	}

	public function toPublicArray()
	{
		$avatarDir = DI::get()->getConfig()->uploads->avatars->wwwfolder.DIRECTORY_SEPARATOR;

		return [
			self::USER_ID => $this->getUserId(),
			self::NAME => $this->getName(),
			self::TIM => $this->getTim()->getName(),
			self::SEX => $this->getSex()->getName(),
			self::AVATAR.'Thumb' => $this->getAvatarThumb() ? $avatarDir.$this->getAvatarThumb() : null,
			self::AVATAR.'Img' => $this->getAvatarImg() ? $avatarDir.$this->getAvatarImg() : null,
			self::CITY => $this->getCity(),
			self::BIRTH => date('Y') - $this->getBirthday(),
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

	protected function getForeignProperties()
	{
		return [];
	}
}

