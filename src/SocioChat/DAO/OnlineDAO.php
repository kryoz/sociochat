<?php

namespace SocioChat\DAO;

use Core\TSingleton;
use Memcached;
use SocioChat\Clients\User;
use SocioChat\DI;

class OnlineDAO
{
	use TSingleton;

	const KEY = 'sociochat.online.list';
	/**
	 * @var Memcached
	 */
	private $memcache;

    public function __construct()
    {
	    $this->memcache = DI::get()->getMemcache()->instance();
    }

	public static function create()
	{
		return static::get();
	}

	public function setOnlineList($channelId = 1)
	{
		$key = self::KEY.$channelId;
		$users = DI::get()->getUsers()->getUsersByChatId($channelId);

		if (count($users) == 0) {
			$this->memcache->delete($key);
			return $this;
		}

		$list = [];
		foreach ($users as $user) {
			$list[$user->getId()] = $user->getProperties()->getName();
		}
		$this->memcache->set($key, json_encode($list));

		return $this;
	}

	/**
	 * @param int $channelId
	 * @return array
	 */
	public function getOnlineList($channelId = 1)
	{
		$list = $this->memcache->get(self::KEY.$channelId);
		if (!$list = json_decode($list, 1)) {
			return [];
		}
		return $list;
	}

	/**
	 * @param int $channelId
	 * @return int
	 */
	public function getOnlineCount($channelId = 1)
	{
		return count($this->getOnlineList($channelId));
	}

	/**
	 * @param $channelId
	 * @param $userId
	 * @return bool
	 */
	public function isUserOnline($channelId, $userId)
	{
		$key = self::KEY.$channelId;
		$list = $this->memcache->get($key);
		if (!$list = json_decode($list, 1)) {
			return false;
		}

		return isset($list[$userId]);
	}
}

