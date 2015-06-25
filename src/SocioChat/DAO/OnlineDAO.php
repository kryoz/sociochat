<?php

namespace SocioChat\DAO;

use Core\TSingleton;
use Memcached;
use SocioChat\DI;

class OnlineDAO
{
	use TSingleton;

	const KEY = 'sociochat.online.';
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

    public function addOne($channelId, $userId)
    {
	    $key = self::KEY.'list'.$channelId;
	    $list = $this->memcache->get($key);
	    if (!$list = json_decode($list, 1)) {
		    $list = [];
	    }

	    $list[$userId] = 1;
	    $this->memcache->set($key, json_encode($list));

	    return $this;
    }

	public function dropOne($channelId, $userId)
	{
		$key = self::KEY.'list'.$channelId;
		$list = $this->memcache->get($key);
		if (!$list = json_decode($list, 1)) {
			return $this;
		}

		unset($list[$userId]);

		if (count($list) == 0) {
			$this->memcache->delete($key);
			return $this;
		}

		$this->memcache->set($key, json_encode($list));

		return $this;
	}

	public function setOnline($channelId, $count)
	{
		if (!$count) {
			$this->memcache->delete(self::KEY.$channelId);
		} else {
			$this->memcache->set(self::KEY.$channelId, $count);
		}

		return $this;
	}

	/**
	 * @param int $channelId
	 * @return int
	 */
	public function getOnlineCount($channelId = 1)
	{
		return $this->memcache->get(self::KEY.$channelId);
	}

	/**
	 * @param $channelId
	 * @param $userId
	 * @return bool
	 */
	public function isUserOnline($channelId, $userId)
	{
		$key = self::KEY.'list'.$channelId;
		$list = $this->memcache->get($key);
		if (!$list = json_decode($list, 1)) {
			return false;
		}

		return isset($list[$userId]);
	}
}

