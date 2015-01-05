<?php

namespace SocioChat\Message\Commands;

use SocioChat\Clients\User;

class Me implements TextCommand
{

	public function isAllowed(User $user)
	{
		return true;
	}

	public function getHelp()
	{
		return '<текст> - выразить действие от 3-го лица';
	}

	public function run(User $user, $args)
	{
		return ['<b><i>'.$user->getProperties()->getName().' '.$args.'</i></b>', false];
	}
}