<?php

namespace SocioChat\Message\Commands;

use SocioChat\Clients\User;
use SocioChat\Controllers\Helpers\RespondError;
use SocioChat\DI;

class GetIp implements TextCommand
{

	public function isAllowed(User $user)
	{
		return $user->getRole()->isAdmin();
	}

	public function getHelp()
	{
		return '<user-name> - lookup IP address';
	}

	public function run(User $user, $args)
	{
		$args = explode(' ', $args);

		$name = $args[0];
		$users = DI::get()->getUsers();

		if (!$targetUser = $users->getClientByName($name)) {
			RespondError::make($user, ['userId' => "$name not found"]);
			return;
		}

		return [$targetUser->getProperties()->getName() . ' ip = ' . $targetUser->getIp(), true];
	}
}