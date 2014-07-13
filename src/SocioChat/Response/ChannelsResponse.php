<?php

namespace SocioChat\Response;

use SocioChat\Clients\ChannelsCollection;

class ChannelsResponse extends Response
{
	protected $channels = [];
	protected $privateProperties = ['privateProperties', 'from', 'recipient'];

	public function setChannels(ChannelsCollection $channels)
	{
		foreach ($channels->getChannels() as $channel) {
			if (!$channel->isPrivate()) {
				$this->channels[$channel->getId()] = $channel->getName();
			}
		}
		return $this;
	}
}
