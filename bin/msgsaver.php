<?php
use SocioChat\Clients\ChannelsCollection;
use SocioChat\DI;

$logger = DI::get()->getLogger();
$memcache = DI::get()->getMemcache();
$channels = ChannelsCollection::get();

$logger->info('Restoring history from memcache');
$memcache->get('sociochat.channels', $json);

if ($list = json_decode($json, 1)) {
	foreach ($list as $id => $channelInfo) {
		$channel = $channels->getChannelById($id);
		if (null === $channel) {
			$logger->info('Creating channel id = '.$id);
			$channels->addChannel(new \SocioChat\Clients\Channel($id, $channelInfo['name'], $channelInfo['isPrivate']));
		}

		$logger->info('Loading messages in channelId '.$id);
		$logger->info(print_r($channelInfo['responses'], 1));
		foreach ($channelInfo['responses'] as $response) {
			$channel->pushRawResponse($response);
		}
		$channel->setLastMsgId($channelInfo['lastMsgId']);
	}
}



$saverCallback = function () use ($config, $logger, $memcache, $channels) {
	$logger->info('Dumping chat log to memcached');
	$memcache->set('sociochat.channels', json_encode($channels->exportChannels()));
};

$timer = $loop->addPeriodicTimer($config->chatlog->memcacheInterval, $saverCallback);