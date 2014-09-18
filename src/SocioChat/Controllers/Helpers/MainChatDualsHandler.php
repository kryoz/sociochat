<?php

namespace SocioChat\Controllers\Helpers;


use SocioChat\Clients\PendingDuals;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Message\MsgToken;
use SocioChat\Response\MessageResponse;

class MainChatDualsHandler
{
	public static function run(User $user, UserCollection $users)
	{
		$duals = PendingDuals::get();

		if ($duals->deleteByUser($user)) {
			self::informOnPendingExit($user);
			$userList = $duals->getUsersByTim($user);
			self::sendRenewPositions($userList, $users);
		}
	}

	private static function informOnPendingExit(User $user)
	{
		$response = (new MessageResponse())
			->setChannelId($user->getChannelId())
			->setTime(null)
			->setDualChat('exit')
			->setMsg(MsgToken::create('ExitDualQueue'));

		(new UserCollection())
			->attach($user)
			->setResponse($response)
			->notify(false);
	}

	private static function sendRenewPositions(array $userIds, UserCollection $users)
	{
		if (empty($userIds)) {
			return;
		}

		$notification = new UserCollection();

		foreach ($userIds as $userId) {
			$user = $users->getClientById($userId);
			$response = (new MessageResponse())
				->setGuests(UserCollection::get()->getUsersByChatId($user->getChannelId()))
				->setChannelId($user->getChannelId());

			$notification
				->attach($user)
				->setResponse($response);
		}

		$notification->notify(false);
	}
} 