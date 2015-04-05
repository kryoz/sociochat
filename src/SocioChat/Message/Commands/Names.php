<?php

namespace SocioChat\Message\Commands;

use SocioChat\Clients\User;
use SocioChat\Controllers\Helpers\RespondError;
use SocioChat\DAO\NameChangeDAO;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\DI;

class Names implements TextCommand
{

	public function isAllowed(User $user)
	{
		return true;
	}

	public function getHelp()
	{
		return '<ник> - показать историю изменений ника пользователя';
	}

	public function run(User $user, $args)
	{
		$request = explode(' ', $args, 1);

		$name = $request[0];

		if (!$targetUser = PropertiesDAO::create()->getByUserName($name)) {
			RespondError::make($user, ['name' => "$name не найден"]);
			return;
		}

		$list = NameChangeDAO::create()->getHistoryByUserId($targetUser->getUserId());

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