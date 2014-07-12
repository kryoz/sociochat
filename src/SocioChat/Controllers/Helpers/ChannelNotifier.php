<?php

namespace SocioChat\Controllers\Helpers;

use SocioChat\Clients\ChannelsCollection;
use SocioChat\Clients\PendingDuals;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Message\MsgToken;
use SocioChat\Response\HistoryResponse;
use SocioChat\Response\MessageResponse;

class ChannelNotifier
{

	public static function welcome(User $user, UserCollection $userCollection, $chatId, $override = false)
	{
		$response = (new MessageResponse())
			->setTime(null)
			->setGuests($userCollection->getUsersByChatId($chatId))
			->setChatId($chatId);

		if ($user->getLastMsgId() == 0 || $override) {
			$response->setMsg(MsgToken::create('WelcomeUser', $userCollection->getClientsCount($chatId), $user->getProperties()->getName()));
		}
		$userCollection
			->setResponse($response)
			->notify();

		self::notifyOnPendingDuals($user);
	}

	public static function indentifyChat(User $user, $channelId)
	{
		$response = (new MessageResponse())
			->setTime(null)
			->setChatId($channelId);

		$response->setMsg(MsgToken::create('IdentifyChannel', $channelId));

		(new UserCollection)
			->attach($user)
			->setResponse($response)
			->notify(false);
	}

	public static function notifyOnPendingDuals(User $user)
	{
		if (!empty(PendingDuals::get()->getUsersByDualTim($user->getProperties()->getTim()))) {
			$response = (new MessageResponse())
				->setMsg(MsgToken::create('DualIsWanted', $user->getProperties()->getTim()->getShortName()))
				->setTime(null)
				->setChatId($user->getChatId());
			(new UserCollection())
				->attach($user)
				->setResponse($response)
				->notify(false);
		}
	}

	public static function uploadHistory(User $user)
	{
		$history = ChannelsCollection::get();
		$log = $history->getHistory($user);
		$client = (new UserCollection())
			->attach($user);

		$historyResponse = (new HistoryResponse)->setChatId($user->getChatId());

		foreach ($log as $response) {
			/* @var $response MessageResponse */

			if ($response->getToUserName()) {
				$name = $user->getProperties()->getName();
				if (!($response->getToUserName() == $name || $response->getFromName() == $name)) {
					continue;
				}
			}

			$historyResponse->addResponse($response);
		}

		$client->setResponse($historyResponse)->notify(false);
	}
} 