<?php

namespace SocioChat\Clients;

use SocioChat\Response\Response;
use SocioChat\TSingleton;

class ChannelsCollection
{
	use TSingleton;
	/**
	 * @var Channel[]
	 */
	private $chatRooms;

	/**
	 * @param string $roomId
	 * @return $this
	 */
	public function createChannel($roomId)
	{
		if (!isset($this->chatRooms[$roomId])) {
			$this->chatRooms[$roomId] = new Channel($roomId);
		}
		return $this;
	}

	public function addChannel(Channel $channel)
	{
		if (!isset($this->chatRooms[$channel->getId()])) {
			$this->chatRooms[$channel->getId()] = $channel;
		}
		return $this;
	}

	/**
	 * @param User $user
	 */
	public function clean(User $user)
	{
		$roomId = $user->getChatId();
		if (UserCollection::get()->getClientsCount($roomId) == 0) {
			if (isset($this->chatRooms[$roomId]) && $user->isInPrivateChat()) {
				unset($this->chatRooms[$roomId]);
				$user->setChatId(1);
			}
		}
	}

	public function getHistory(User $user)
	{
		if (!isset($this->chatRooms[$user->getChatId()])) {
			return [];
		}

		$room = $this->chatRooms[$user->getChatId()];
		/* @var $room Channel */

		return $room->getHistory($user->getLastMsgId());
	}

	public function pushToHistory(Response $response)
	{
		$this->createChannel($response->getChatId());
		/* @var $room Channel */
		$room = $this->chatRooms[$response->getChatId()];

		return $room->pushResponse($response);
	}

	/**
	 * @param $id
	 * @return Channel|null
	 */
	public function getChannelById($id)
	{
		return isset($this->chatRooms[$id]) ? $this->chatRooms[$id] : null;
	}

	/**
	 * @param $channelName
	 * @return Channel|null
	 */
	public function getChannelByName($channelName)
	{
		foreach ($this->chatRooms as $channel) {
			if ($channel->getName() == $channelName) {
				return $channel;
			}
		}
	}
}