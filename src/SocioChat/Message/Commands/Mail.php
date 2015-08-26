<?php

namespace SocioChat\Message\Commands;

use SocioChat\Clients\User;
use SocioChat\Controllers\Helpers\RespondError;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\DAO\UserDAO;
use SocioChat\DI;
use SocioChat\Permissions\UserActions;

class Mail implements TextCommand
{

	public function isAllowed(User $user)
	{
		return true;
	}

	public function getHelp()
	{
		return '<ник> <сообщение> - отправить пользователю на почту сообщение';
	}

	public function run(User $user, $args)
	{
		$args = explode(' ', $args, 2);
		$userName = $args[0];
		if (!isset($args[1])) {
			RespondError::make($user, ['msg' => 'Вы не ввели сообщения']);
			return;
		}
		$text = $args[1];

		$properties = PropertiesDAO::create()->getByUserName($userName);
		if (!$properties->getId()) {
			RespondError::make($user, ['msg' => "$userName не зарегистрирован или имя введено не верно"]);
			return;
		}

		$address = UserDAO::create()->getById($properties->getUserId());
		$permissions = new UserActions($user->getUserDAO());
		$actions = $permissions->getAllowed($address);

		if (!in_array(UserActions::MAIL, $actions)) {
			RespondError::make($user, ['msg' => $user->getLang()->getPhrase('NoPermission')]);
			return;
		}

		//@TODO сделать отправку по крону
		//также надо ограничить частоту отправки

		$config = DI::get()->getConfig();
		$mailerName = 'СоциоЧат';
		$headers = "MIME-Version: 1.0 \n"
			. "From: " . mb_encode_mimeheader($mailerName)
			. "<" . $config->adminEmail . "> \n"
			. "Reply-To: " . mb_encode_mimeheader($mailerName)
			. "<" . $config->adminEmail . "> \n"
			. "Content-Type: text/html;charset=UTF-8\n";

		$topic = 'Для вас есть сообщение';
		$msg = "<h2>Вам пришло сообщение от пользователя {$user->getProperties()->getName()}</h2>";
		$msg .= '<p>'.htmlentities(strip_tags($text)).'</p>';
		$msg .= '<hr>';
		$msg .= 'Вернуться в <a href="'.$config->domain->protocol.$config->domain->web.'">СоциоЧат</a>';

		mb_send_mail($address->getEmail(), $topic, $msg, $headers);

		RespondError::make($user, ['msg' => 'Сообщение отправлено!']);
		return ['Сообщение отправлено!', true];
	}
}