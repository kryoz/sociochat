<?php

namespace SocioChat\Message\Commands;

use SocioChat\Clients\User;
use SocioChat\Controllers\Helpers\RespondError;
use SocioChat\DAO\NameChangeDAO;
use SocioChat\DI;

class Names implements TextCommand
{

	public function isAllowed(User $user)
	{
		return true;
	}

	public function getHelp()
	{
		return '<ник-пользователя> - показать историю изменений ника пользователя';
	}

	public function run(User $user, $args)
	{
		$request = explode(' ', $args);

		$name = $request[0];
		$users = DI::get()->getUsers();

		if (!$targetUser = $users->getClientByName($name)) {
			RespondError::make($user, ['userId' => "$name not found"]);
			return;
		}

		$list = NameChangeDAO::create()->getHistoryByUserId($targetUser->getId());

		$html = '<table class="table table-striped">';

		/** @var $row NameChangeDAO */
		foreach ($list as $row) {
			$html .= '<tr>';
			$html .= '<td>' . $row->getDateRaw() . '</td>';
			$html .= '<td>' . $row->getName() . '</td>';
			$html .= '</tr>';
		}
		$html .= '</table>';

		return [$html, true];
	}
}