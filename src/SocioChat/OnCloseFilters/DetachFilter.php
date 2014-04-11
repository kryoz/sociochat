<?php

namespace SocioChat\OnCloseFilters;

use SocioChat\Chain\ChainContainer;
use SocioChat\Chain\ChainInterface;
use SocioChat\ChatConfig;
use SocioChat\Clients\ChatsCollection;
use SocioChat\Log;
use SocioChat\MightyLoop;
use SocioChat\Clients\PendingDuals;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Response\MessageResponse;
use SocioChat\Utils\Lang;


class DetachFilter implements ChainInterface
{
	public function handleRequest(ChainContainer $chain)
	{
		$clients = UserCollection::get();
		$conn = $chain->getFrom()->getConnectionId();

		if (!$user = $clients->getClientByConnectionId($conn)) {
			return;
		}

		/* @var $user User */
		$this->handleDisconnection($user);
	}

	private function handleDisconnection(User $user)
	{
		$loop = MightyLoop::get()->fetch();
		$logger = Log::get()->fetch();
		$detacher = function() use ($user, $logger) {
			$clients = UserCollection::get();
			$clients->detach($user);
			$logger->info("OnClose: close connId = {$user->getConnectionId()} userId = {$user->getId()}\nTotal user count {$clients->getTotalCount()}", [__CLASS__]);

			$this->notifyOnClose($user, $clients);
			$this->cleanPendingQueue($user);

			ChatsCollection::get()->clean($user);

			$user->save();
			unset($user);
		};

		if ($user->isAsyncDetach()) {
			$timeout = ChatConfig::get()->getConfig()->session->timeout;
			$logger->info("OnClose: Detach deffered in $timeout sec for user_id = {$user->getId()}...", [__CLASS__]);
			$timer = $loop->addTimer($timeout, $detacher);
			$user->setTimer($timer);
		} else {
			$logger->info("OnClose: Detached instantly...", [__CLASS__]);
			$detacher();
		}

		$user->getConnection()->close();
	}

	private function notifyOnClose(User $user, UserCollection $clients)
	{
		$response = new MessageResponse();

		if ($user->isAsyncDetach()) {
			$response->setMsg(Lang::get()->getPhrase('LeavesUs', $user->getProperties()->getName()));
		}

		$response
			->setTime(null)
			->setGuests($clients->getUsersByChatId($user->getChatId()))
			->setChatId($user->getChatId());

		$clients
			->setResponse($response)
			->notify();
	}

	private function cleanPendingQueue(User $user)
	{
		$duals = PendingDuals::get();
		$duals->deleteByUserId($user->getId());
	}
}