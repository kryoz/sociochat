<?php

namespace SocioChat\Session;

use SocioChat\Clients\User;
use Core\TSingleton;

class MemorySessionHandler implements SessionHandler
{
	use \Core\TSingleton;
	private $storage = [];

	public function read($id)
	{
		return isset($this->storage[$id]) ? json_decode($this->storage[$id]['data'], 1) : false;
	}

	public function store($id, $userId)
	{
		$this->storage[$id]= [
			'time' => date('Y-m-d H:i:s'),
			'data'=> $userId
		];
	}

	public function clean($ttl)
	{
		$old = date('Y-m-d H:i:s', time() - $ttl);
		foreach ($this->storage as $id => $stack) {
			if ($stack['time'] < $old) {
				unset($this->storage[$id]);
			}
		}
	}

	public function updateSessionId(User $user, $oldUserId)
	{
		// TODO: Implement delete() method.
	}
}