<?php

namespace SocioChat\OnCloseFilters;

use Core\DI;
use SocioChat\Chain\ChainContainer;
use SocioChat\Chain\ChainInterface;
use SocioChat\Clients\ChannelsCollection;
use SocioChat\Clients\PendingDuals;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Controllers\Helpers\ChannelNotifier;
use SocioChat\Message\MsgToken;
use SocioChat\Response\MessageResponse;


class DetachFilter implements ChainInterface
{
	public function handleRequest(ChainContainer $chain)
	{
		$user = $chain->getFrom();
		$loop = DI::get()->container()->get('eventloop');
		$logger = DI::get()->getLogger();

		$detacher = function() use ($user, $logger) {
			$clients = UserCollection::get();
			$clients->detach($user);
			$logger->info("OnClose: close connId = {$user->getConnectionId()} userId = {$user->getId()}\nTotal user count {$clients->getTotalCount()}", [__CLASS__]);

			$this->notifyOnClose($user, $clients);
			$this->cleanPendingQueue($user);

			ChannelsCollection::get()->clean($user);

			$user->save();
			unset($user);
		};

		if ($user->isAsyncDetach()) {
			$timeout = DI::get()->getConfig()->session->timeout;
			$logger->info("OnClose: Detach deffered for $timeout sec for user_id = {$user->getId()}...", [__CLASS__]);
			$timer = $loop->addTimer($timeout, $detacher);
			$user->setDisconnectTimer($timer);
		} else {
			$logger->info("OnClose: Detached instantly user_id {$user->getId()}...", [__CLASS__]);
			$detacher();
		}

		$user->getConnection()->close();
	}

	private function notifyOnClose(User $user, UserCollection $clients)
	{
		$response = new MessageResponse();

		if ($user->isAsyncDetach()) {
			$response->setMsg(MsgToken::create('LeavesUs', $user->getProperties()->getName()));
		}

		$response
			->setTime(null)
			->setGuests($clients->getUsersByChatId($user->getChannelId()))
			->setChannelId($user->getChannelId());

		$clients
			->setResponse($response)
			->notify();

		ChannelNotifier::updateChannelInfo($clients, ChannelsCollection::get());
	}

	private function cleanPendingQueue(User $user)
	{
		$duals = PendingDuals::get();
		$duals->deleteByUser($user);
	}
}