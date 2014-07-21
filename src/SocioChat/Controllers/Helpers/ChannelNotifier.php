<?php

namespace SocioChat\Controllers\Helpers;

use SocioChat\Clients\Channel;
use SocioChat\Clients\ChannelsCollection;
use SocioChat\Clients\PendingDuals;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Message\MsgToken;
use SocioChat\Response\ChannelsResponse;
use SocioChat\Response\HistoryResponse;
use SocioChat\Response\MessageResponse;

class ChannelNotifier
{

	public static function welcome(User $user, UserCollection $userCollection)
	{
		if ($user->getLastMsgId() == 0) {
			$channelId = $user->getChannelId();
			$response = (new MessageResponse())
				->setTime(null)
				->setChannelId($channelId)
				->setMsg(MsgToken::create('WelcomeUser', $userCollection->getClientsCount($channelId), $user->getProperties()->getName()));
			$userCollection
				->setResponse($response)
				->notify();
		}

		self::notifyOnPendingDuals($user);
	}

	public static function indentifyChat(User $user, UserCollection $userCollection, $silent = false)
	{
		$channels = ChannelsCollection::get();
		$channelId = $user->getChannelId();

		if (!$silent) {
			$response = (new MessageResponse())
				->setTime(null)
				->setChannelId($channelId);

			$channel = $channels->getChannelById($channelId);
			$response->setMsg(MsgToken::create('IdentifyChannel', $channel->getName()));

			(new UserCollection)
				->attach($user)
				->setResponse($response)
				->notify(false);
		}

		// Update user channel info
		$response = (new ChannelsResponse())
			->setChannels($channels)
			->setChannelId($channelId);

		(new UserCollection)
			->attach($user)
			->setResponse($response)
			->notify(false);

		// Refresh everybody's guest list in the new channel
		self::updateGuestsList($userCollection, $channelId);
	}

	public static function updateGuestsList(UserCollection $userCollection, $channelId)
	{
		$userCollection
			->setResponse(
				(new MessageResponse)
					->setGuests($userCollection->getUsersByChatId($channelId))
					->setChannelId($channelId)
			)
			->notify(false);
	}

	public static function notifyOnPendingDuals(User $user)
	{
		if (!empty(PendingDuals::get()->getUsersByDualTim($user->getProperties()->getTim()))) {
			$response = (new MessageResponse())
				->setMsg(MsgToken::create('DualIsWanted', $user->getProperties()->getTim()->getShortName()))
				->setTime(null)
				->setChannelId($user->getChannelId());
			(new UserCollection())
				->attach($user)
				->setResponse($response)
				->notify(false);
		}
	}

	public static function uploadHistory(User $user, $clear = null)
	{
		$channel = ChannelsCollection::get()->getChannelById($user->getChannelId());

		if (!$channel) {
			$channel = new Channel($user->getChannelId(), 'Приват_'.$user->getChannelId());
			ChannelsCollection::get()->addChannel($channel);
		}
		$log = $channel->getHistory($user->getLastMsgId());

		$client = (new UserCollection())
			->attach($user);

		$historyResponse = (new HistoryResponse)
			->setChannelId($user->getChannelId());

		if ($user->getLastMsgId() > 0) {
			$historyResponse->setClear($clear);
		}

		foreach ($log as $response) {
			if (isset($response[Channel::TO_NAME])) {
				$name = $user->getProperties()->getName();
				if ($response[Channel::TO_NAME] == $name || $response[Channel::FROM_NAME] == $name) {
					$historyResponse->addResponse($response);
				}
				continue;
			}

			$historyResponse->addResponse($response);
		}

		$historyResponse->setLastMsgId($channel->getLastMsgId());

		$client
			->setResponse($historyResponse)
			->notify(false);
	}
} 