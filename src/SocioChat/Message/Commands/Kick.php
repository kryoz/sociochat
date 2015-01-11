<?php

namespace SocioChat\Message\Commands;


use SocioChat\Application\Chat;
use SocioChat\Clients\User;
use SocioChat\DI;

class Kick implements TextCommand
{

	public function isAllowed(User $user)
	{
		return $user->getRole()->isAdmin() || $user->isCreator();
	}

	public function getHelp()
	{
		return '<ник> <сообщение> - кикнуть посетителя';
	}

	public function run(User $user, $args)
	{
		$text = explode(' ', $args, 2);

		$assHoleName = $text[0];
		$users = DI::get()->getUsers();

		if (!$assHole = $users->getClientByName($assHoleName)) {
			return ["$assHoleName not found", 1];
		}

		$assHole
			->setAsyncDetach(false)
			->send(
				[
					'disconnect' => 1,
					'msg' => isset($text[1]) ? $text[1] : null
				]
			);

		Chat::get()->onClose($assHole->getConnection());

		return ["$assHoleName кикнут", false];
	}
}