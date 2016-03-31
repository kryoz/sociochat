<?php

namespace SocioChat\Message\Commands;

use Core\Utils\DbQueryHelper;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Controllers\Helpers\RespondError;
use SocioChat\DAO\PropertiesDAO;
use SocioChat\DAO\UserKarmaDAO;
use SocioChat\DI;
use SocioChat\Response\MessageResponse;

class Karma implements TextCommand
{

	public function isAllowed(User $user)
	{
		return $user->getRole()->isAdmin();
	}

	public function getHelp()
	{
		return '- changes karma';
	}

	public function run(User $user, $args)
	{
		$args = explode(' ', $args, 2);
		$userName = $args[0];
		if (!isset($args[1])) {
			RespondError::make($user, ['msg' => 'Вы не ввели сообщения']);
			return;
		}
		
		$karmaMark = (int) $args[1];
		$users = DI::get()->getUsers();
		$subject = null;
		
		if (!$subject = $users->getClientByName($userName)) {
			$properties = PropertiesDAO::create()->getByUserName($userName);
			if (!$properties->getId()) {
				RespondError::make($user, ['msg' => "$userName не зарегистрирован или имя введено не верно"]);
				return;
			}
			$subject = UserCollection::get()->getClientById($properties->getUserId());
		} else {
			$properties = $subject->getProperties();
		}

		$karma = UserKarmaDAO::create()->getKarmaByUserId($properties->getUserId());

		$properties
			->setKarma($karma+$karmaMark)
			->save();

		$mark = UserKarmaDAO::create()
			->setUserId($properties->getUserId())
			->setEvaluator($user)
			->setMark($karmaMark)
			->setDateRegister(DbQueryHelper::timestamp2date());
		$mark->save();

		$chatId = $subject ? $subject->getChannelId() : $user->getChannelId();

		$response = (new MessageResponse())
			->setGuests($users->getUsersByChatId($chatId))
			->setChannelId($chatId)
			->setTime(null);

		DI::get()->getUsers()
			->setResponse($response)
			->notify();

		return ['Karma to user '.$userName.' was changed to '.($karma+$karmaMark), true];
	}
}