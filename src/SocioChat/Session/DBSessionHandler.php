<?php

namespace SocioChat\Session;

use SocioChat\Clients\User;
use SocioChat\DAO\NameChangeDAO;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\DAO\SessionDAO;
use SocioChat\DAO\UserBlacklistDAO;
use SocioChat\DAO\UserDAO;
use Core\TSingleton;

class DBSessionHandler implements SessionHandler
{
	use \Core\TSingleton;

	const TIMESTAMP = 'Y-m-d H:i:s';

	/**
	 * @param $sessionId
	 * @return SessionDAO|null
	 */
	public function read($sessionId)
	{
		$session = SessionDAO::create()->getBySessionId($sessionId);
		return $session->getId() ? $session : null;
	}

	public function store($sessionId, $userId)
	{
		$session = SessionDAO::create();

		$session->getBySessionId($sessionId);

		$session
			->setSessionId($sessionId)
			->setAccessTime(date(self::TIMESTAMP))
			->setUserId($userId);

		$session->save();
	}

	public function clean($ttl)
	{
		$deadLine = date(self::TIMESTAMP, time() - $ttl);
		$users = SessionDAO::create()->getObsoleteUserIds($deadLine);

		if (!empty($users)) {
			SessionDAO::create()->dropByUserIdList($users);
			UserDAO::create()->dropByUserIdList($users);
			PropertiesDAO::create()->dropByUserIdList($users);
			UserBlacklistDAO::create()->dropByUserIdList($users);
		}
	}

	public function updateSessionId(User $user, $oldUserId)
	{
		SessionDAO::create()->dropByUserId($oldUserId);

		$session = SessionDAO::create()->getByUserId($user->getId());
		$session
			->setSessionId($user->getWSRequest()->getCookie('token'))
			->setAccessTime(date(self::TIMESTAMP))
			->setUserId($user->getId());

		$session->save();

		PropertiesDAO::create()->dropByUserId($oldUserId);
		UserBlacklistDAO::create()->dropByUserId($oldUserId);
		NameChangeDAO::create()->dropByUserId($oldUserId);
		UserDAO::create()->dropById($oldUserId);
	}
}