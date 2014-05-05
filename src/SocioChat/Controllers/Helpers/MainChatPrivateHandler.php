<?php

namespace SocioChat\Controllers\Helpers;


use SocioChat\Clients\ChatsCollection;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Message\MsgToken;
use SocioChat\Response\MessageResponse;

class MainChatPrivateHandler
{
	public static function run(User $user, UserCollection $users, ChatsCollection $chats)
	{
		if (!$user->isInPrivateChat()) {
			return;
		}

		self::moveUsersToPublic($user, $users);
		self::informYouselfOnExit($user);
		self::refreshGuestListOnNewChat($user, $users);

		$chats->clean($user);
		$user->save();
	}

	/**
	 * @param User $user
	 * @param UserCollection $users
	 */
	private static function moveUsersToPublic(User $user, UserCollection $users)
	{
		$oldChatId = $user->getChatId();
		$user->setChatId(1);

		$partners = $users->getUsersByChatId($user->getChatId());

		$response = (new MessageResponse())
			->setTime(null)
			->setMsg(MsgToken::create('UserLeftPrivate', $user->getProperties()->getName()))
			->setDualChat('exit')
			->setGuests($partners)
			->setChatId($oldChatId);

		$users
			->setResponse($response)
			->notify();

		foreach ($partners as $pUser) {
			$pUser->setChatId(1);
			$pUser->save();
		}
	}

	private function refreshGuestListOnNewChat(User $user, UserCollection $users)
	{
		$response = (new MessageResponse())
			->setTime(null)
			->setGuests($users->getUsersByChatId(1))
			->setChatId($user->getChatId());

		$users
			->setResponse($response)
			->notify(false);
	}

	private function informYouselfOnExit(User $user)
	{
		$response = (new MessageResponse())
			->setChatId($user->getChatId())
			->setTime(null)
			->setDualChat('exit')
			->setMsg(MsgToken::create('ReturnedToMainChat'));

		(new UserCollection())
			->attach($user)
			->setResponse($response)
			->notify(false);
	}


} 