<?php

namespace SocioChat\Message\Commands;

use SocioChat\Clients\User;
use SocioChat\Controllers\Helpers\RespondError;
use SocioChat\DI;

class Sync implements TextCommand
{

	public function isAllowed(User $user)
	{
		return $user->getRole()->isAdmin();
	}

	public function getHelp()
	{
		return '- save all users data to DB';
	}

	public function run(User $user, $args)
	{
		$users = DI::get()->getUsers()->getAll();
		$logger = DI::get()->getLogger();
		/** @var User $user */
		foreach ($users as $user) {
			$user->save(true);
			$logger->info('User data for '.$user->getId().' is saved.');
		}

		return ['User data has flushed to disk!', true];
	}
}