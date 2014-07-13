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
	private $channels;

	/**
	 * @param string $channelId
	 * @return $this
	 */
	public function createChannel($channelId)
	{
		if (!isset($this->channels[$channelId])) {
			$this->channels[$channelId] = new Channel($channelId);
		}
		return $this;
	}

	public function addChannel(Channel $channel)
	{
		if (!isset($this->channels[$channel->getId()])) {
			$this->channels[$channel->getId()] = $channel;
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
			if (isset($this->channels[$roomId]) && $user->isInPrivateChat()) {
				unset($this->channels[$roomId]);
				$user->setChatId(1);
			}
		}
	}

	public function getHistory(User $user)
	{
		if (!isset($this->channels[$user->getChatId()])) {
			return [];
		}

		$room = $this->channels[$user->getChatId()];
		/* @var $room Channel */

		return $room->getHistory($user->getLastMsgId());
	}

	public function pushToHistory(Response $response)
	{
		$this->createChannel($response->getChanelId());
		/* @var $room Channel */
		$room = $this->channels[$response->getChanelId()];

		return $room->pushResponse($response);
	}

	/**
	 * @param $id
	 * @return Channel|null
	 */
	public function getChannelById($id)
	{
		return isset($this->channels[$id]) ? $this->channels[$id] : null;
	}

	/**
	 * @param $channelName
	 * @return Channel|null
	 */
	public function getChannelByName($channelName)
	{
		foreach ($this->channels as $channel) {
			if ($channel->getName() == $channelName) {
				return $channel;
			}
		}
	}

	public function getChannels()
	{
		return $this->channels;
	}
}