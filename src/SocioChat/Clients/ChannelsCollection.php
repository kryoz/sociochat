<?php

namespace SocioChat\Clients;

use SocioChat\Response\MessageResponse;
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
		$roomId = $user->getChannelId();
		if (UserCollection::get()->getClientsCount($roomId) == 0 && isset($this->channels[$roomId])) {
			unset($this->channels[$roomId]);
		}
	}

	public function getHistory(User $user)
	{
		if (!isset($this->channels[$user->getChannelId()])) {
			return [];
		}

		$channel = $this->channels[$user->getChannelId()];
		/* @var $channel Channel */

		return $channel->getHistory($user->getLastMsgId());
	}

	public function pushToHistory(MessageResponse $response)
	{
		if (!isset($this->channels[$response->getChannelId()])) {
			throw new \Exception('Channel id = '.$response->getChannelId().' has not been initialized');
		}
		/* @var $channel Channel */
		$channel = $this->channels[$response->getChannelId()];

		return $channel->pushResponse($response);
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