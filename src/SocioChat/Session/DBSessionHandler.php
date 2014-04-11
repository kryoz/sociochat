<?php

namespace SocioChat\Session;

use SocioChat\Clients\User;
use SocioChat\DAO\SessionDAO;
use SocioChat\DB;
use SocioChat\TSingleton;

class DBSessionHandler implements SessionHandler
{
	use TSingleton;
	/**
	 * @var DB
	 */
	private $db;

	public function __construct()
	{
		$this->db = DB::get();
	}

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
			->setAccessTime(date('Y-m-d H:i:s'))
			->setUserId($userId);

		$session->save();
	}

	public function clean($ttl)
	{
		$old = date('Y-m-d H:i:s', time() - $ttl);
		$users = $this->db->query('SELECT s.user_id AS user_id FROM sessions AS s JOIN users AS u ON u.id = s.user_id WHERE s.access < ? AND u.email IS NULL', [$old]);

		if (!empty($users)) {
			$users = array_column($users, 'user_id');
			$usersList = '('.implode(', ', $users).')';

			$this->db->exec('DELETE FROM sessions WHERE access < ?', [$old]);
			$this->db->exec("DELETE FROM users WHERE id IN $usersList");
			$this->db->exec("DELETE FROM user_properties WHERE user_id IN $usersList");
			$this->db->exec("DELETE FROM user_blacklist WHERE user_id IN $usersList OR ignored_user_id IN $usersList");
		}
	}

	public function updateSessionId(User $user, $oldUserId)
	{

		$this->db->exec('DELETE FROM sessions WHERE user_id = ?', [$oldUserId]);

		$session = SessionDAO::create();
		$session->getByUserId($user->getId());
		$session
			->setSessionId($user->getWSRequest()->getCookie('PHPSESSID'))
			->setAccessTime(date('Y-m-d H:i:s'))
			->setUserId($user->getId());
		$session->save();

		$this->db->exec('DELETE FROM users WHERE id = ?', [$oldUserId]);
		$this->db->exec('DELETE FROM user_properties WHERE user_id = ?', [$oldUserId]);
		$this->db->exec('DELETE FROM user_blacklist WHERE user_id = ? OR ignored_user_id = ?', [$oldUserId, $oldUserId]);
	}
}