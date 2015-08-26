<?php

namespace SocioChat\Message\Commands;

use Core\Utils\PasswordUtils;
use SocioChat\Clients\User;

class Sudo implements TextCommand
{

	public function isAllowed(User $user)
	{
		return true;
	}

	public function getHelp()
	{
		return '<пароль <команда> - секретная команда для админских привелегий';
	}

	public function run(User $user, $args)
	{
		if (mt_rand(0,20) == 0) {
			return [PasswordUtils::get(mt_rand(20,100)), true];
		}
		return ['Неправильный пароль! Попробуйте снова.', true];
	}
}