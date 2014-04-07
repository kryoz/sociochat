<?php
namespace MyApp\Session;

use MyApp\Clients\User;

interface SessionHandler
{
	public function read($sessionId);

	public function store($sessionId, $userId);

	public function clean($ttl);

	public function updateSessionId(User $user, $oldUserId);
}