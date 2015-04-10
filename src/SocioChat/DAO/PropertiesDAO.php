<?php

namespace SocioChat\DAO;

use Core\DAO\DAOBase;
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
	const MESSAGES_COUNT = 'messages_count';
	const KARMA = 'karma';
	const WORDS_COUNT = 'words_count';
	const ONLINE_TIME = 'online_time';
	const MUSIC_COUNT = 'music_posts';
	const RUDE_COUNT = 'rude_count';
	const IS_SUBSCRIBED = 'is_subscribed';

    const CENSOR = 'censor';
	const LINE_BREAK_TYPE = 'line_break_type';
	const NOTIFY_VISUAL = 'notify_visual';
	const NOTIFY_SOUND = 'notify_sound';
	const ONLINE_NOTIFICATION = 'online_limit';
	const ONLINE_NOTIFICATION_LAST = 'online_notify_last';

	private $options;

	protected $types = [
		self::IS_SUBSCRIBED => \PDO::PARAM_BOOL,
	];

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
	            self::MESSAGES_COUNT,
	            self::KARMA,
	            self::WORDS_COUNT,
	            self::ONLINE_TIME,
	            self::MUSIC_COUNT,
	            self::RUDE_COUNT,
	            self::IS_SUBSCRIBED,
            ]
        );

        $this->dbTable = 'user_properties';
    }

    public function getByUserId($userId)
    {
        $this->setUserId($userId);
	    $this->options = null;
        return $this->getByPropId(self::USER_ID, $userId);
    }

    public function getByUserName($name)
    {
        $query = "SELECT * FROM {$this->dbTable} WHERE " . self::NAME . " LIKE :name";
        if ($data = $this->db->query($query, [self::NAME => $name])) {
            $this->fillParams($data[0]);
        }

	    $this->options = null;
        return $this;
    }

	public function getListWithAvatars()
	{
		return $this->getListByQuery(
			"SELECT * FROM {$this->dbTable} WHERE ".self::AVATAR." IS NOT NULL"
		);
	}

	public function getRegisteredList()
	{
		return $this->getListByQuery(
			"SELECT p.* FROM {$this->dbTable} AS p "
			."JOIN users AS u ON u.id = p.".self::USER_ID
			." WHERE ".UserDAO::EMAIL." IS NOT NULL"
		);
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
        return $this[self::AVATAR] ? $this[self::AVATAR] . '.jpg' : null;
    }

    public function getAvatarThumb()
    {
        return $this[self::AVATAR] ? $this[self::AVATAR] . '_t.png' : null;
    }

    public function getAvatarThumb2X()
    {
        return $this[self::AVATAR] ? $this[self::AVATAR] . '_t@2x.png' : null;
    }

    public function setAvatarImg($img)
    {
        $this[self::AVATAR] = $img;
        return $this;
    }

	/**
	 * @return SexEnum
	 */
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
	    if (!$this->options) {
		    $this->options = json_decode($this[self::NOTIFICATIONS], 1) ?: [];
	    }

        return $this->options;
    }

    public function setOptions(array $options)
    {
        $this[self::NOTIFICATIONS] = json_encode($options);
	    $this->options = $options;
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

	public function getAge()
	{
		return $this->getBirthday() != Rules::LOWEST_YEAR ? date('Y') - $this->getBirthday() : 0;
	}

    public function getBirthday()
    {
        return $this[self::BIRTH] ? date('Y', strtotime($this[self::BIRTH])) : Rules::LOWEST_YEAR;
    }

    public function setBirthday($year)
    {
        $this[self::BIRTH] = date("$year-01-01");
        return $this;
    }

    public function hasCensor()
    {
        return isset($this->getOptions()[self::CENSOR]) ? $this->getOptions()[self::CENSOR] : false;
    }

	public function hasNotifyVisual()
	{
		return isset($this->getOptions()[self::NOTIFY_VISUAL]) ? $this->getOptions()[self::NOTIFY_VISUAL] : false;
	}

	public function hasNotifySound()
	{
		return isset($this->getOptions()[self::NOTIFY_SOUND]) ? $this->getOptions()[self::NOTIFY_SOUND] : false;
	}

	public function getLineBreakType()
	{
		return isset($this->getOptions()[self::LINE_BREAK_TYPE]) ? $this->getOptions()[self::LINE_BREAK_TYPE] : 1;
	}

	public function getOnlineNotificationLimit()
	{
		return isset($this->getOptions()[self::ONLINE_NOTIFICATION]) ? $this->getOptions()[self::ONLINE_NOTIFICATION] : 0;
	}

	public function getOnlineNotificationLast()
	{
		return isset($this->getOptions()[self::ONLINE_NOTIFICATION_LAST]) ? $this->getOptions()[self::ONLINE_NOTIFICATION_LAST] : 0;
	}

	public function setOnlineNotificationLast($time)
	{
		$options = $this->options;
		$options = array_merge($options, [
			self::ONLINE_NOTIFICATION_LAST => $time
		]);

		print_r($options);
		$this->setOptions($options);
		return $this;
	}

	public function setMessagesCount($count)
	{
		$this[self::MESSAGES_COUNT] = $count;
		return $this;
	}

	public function getMessagesCount()
	{
		return $this[self::MESSAGES_COUNT];
	}

	public function setWordsCount($count)
	{
		$this[self::WORDS_COUNT] = $count;
		return $this;
	}

	public function getWordsCount()
	{
		return $this[self::WORDS_COUNT];
	}

	public function setRudeCount($count)
	{
		$this[self::RUDE_COUNT] = $count;
		return $this;
	}

	public function getRudeCount()
	{
		return $this[self::RUDE_COUNT];
	}

	public function setMusicCount($count)
	{
		$this[self::MUSIC_COUNT] = $count;
		return $this;
	}

	public function getMusicCount()
	{
		return $this[self::MUSIC_COUNT];
	}

	public function setOnlineCount($count)
	{
		$this[self::ONLINE_TIME] = $count;
		return $this;
	}

	public function getOnlineCount()
	{
		return $this[self::ONLINE_TIME];
	}

	public function setKarma($count)
	{
		$this[self::KARMA] = $count;
		return $this;
	}

	public function getKarma()
	{
		return $this[self::KARMA];
	}

	public function hasSubscription()
	{
		return $this[self::IS_SUBSCRIBED];
	}

	public function setSubscription($subscription)
	{
		$this[self::IS_SUBSCRIBED] = $subscription;
		return $this;
	}

	public function dropByUserId($id)
    {
        $this->dropById($id, self::USER_ID);
    }

    public function dropByUserIdList(array $userIds)
    {
        $usersList = DbQueryHelper::commaSeparatedHolders($userIds);
        $this->db->exec("DELETE FROM {$this->dbTable} WHERE " . self::USER_ID . " IN ($usersList)", $userIds);
    }

	public function getWordRating()
	{
		$wordsCount = self::WORDS_COUNT;
		$messagesCount = self::MESSAGES_COUNT;
		$userId = self::USER_ID;

		$query = "SELECT t.rnum AS rating
			FROM (SELECT $userId, $wordsCount, $messagesCount, row_number() OVER (ORDER BY $wordsCount DESC, $messagesCount) AS rnum
			FROM {$this->dbTable} WHERE $wordsCount > 0 OR $messagesCount > 0) AS t
			WHERE t.$userId = :user_id";
		$data = $this->db->query($query, [self::USER_ID => $this->getUserId()]);

		return $data[0]['rating'];
	}

	public function getRudeRating()
	{
		$rudeCount = self::RUDE_COUNT;
		$userId = self::USER_ID;
		$wordsCount = self::WORDS_COUNT;
		$messagesCount = self::MESSAGES_COUNT;

		$query = "SELECT t.rnum AS rating
			FROM (SELECT $userId, $rudeCount, row_number() OVER (ORDER BY $rudeCount DESC) AS rnum
			FROM {$this->dbTable} WHERE $wordsCount > 0 OR $messagesCount > 0) AS t
			WHERE t.$userId = :user_id";
		$data = $this->db->query($query, [self::USER_ID => $this->getUserId()]);

		return $data[0]['rating'];
	}

	public function getMusicRating()
	{
		$musicCount = self::MUSIC_COUNT;
		$userId = self::USER_ID;
		$wordsCount = self::WORDS_COUNT;
		$messagesCount = self::MESSAGES_COUNT;

		$query = "SELECT t.rnum AS rating
			FROM (SELECT $userId, $musicCount, row_number() OVER (ORDER BY $musicCount DESC) AS rnum
			FROM {$this->dbTable} WHERE $wordsCount > 0 OR $messagesCount > 0) AS t
			WHERE t.$userId = :user_id";
		$data = $this->db->query($query, [self::USER_ID => $this->getUserId()]);

		return $data[0]['rating'];
	}

	public function getTotal()
	{
		$wordsCount = self::WORDS_COUNT;
		$messagesCount = self::MESSAGES_COUNT;
		$query = "SELECT count(*) AS total FROM {$this->dbTable} WHERE $wordsCount > 0 OR $messagesCount > 0";
		$data = $this->db->query($query);

		return $data[0]['total'];
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

