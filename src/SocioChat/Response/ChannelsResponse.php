<?php

namespace SocioChat\Response;

use SocioChat\Clients\ChannelsCollection;
use SocioChat\Clients\UserCollection;

class ChannelsResponse extends Response
{
	protected $channels = [];
	protected $privateProperties = ['privateProperties', 'from', 'recipient'];

	public function setChannels(ChannelsCollection $channels)
	{
		$users = UserCollection::get();

		foreach ($channels->getChannels() as $channel) {
			if (!$channel->isPrivate()) {
				$this->channels[$channel->getId()] = [
					'name' => $channel->getName(),
					'usersCount' => $users->getClientsCount($channel->getId())
				];
			}
		}
		return $this;
	}
}
