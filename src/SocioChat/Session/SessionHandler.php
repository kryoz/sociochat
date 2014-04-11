<?php
namespace SocioChat\Session;

use SocioChat\Clients\User;

interface SessionHandler
{
	public function read($sessionId);

	public function store($sessionId, $userId);

	public function clean($ttl);

	public function updateSessionId(User $user, $oldUserId);
}