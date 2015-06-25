<?php

namespace SocioChat\Application\OnCloseFilters;

use Monolog\Logger;
use SocioChat\Application\Chain\ChainContainer;
use SocioChat\Application\Chain\ChainInterface;
use SocioChat\DAO\OnlineDAO;
use SocioChat\DI;
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

        if ($user->isAsyncDetach()) {
            $timeout = DI::get()->getConfig()->session->timeout;
            $logger->info("OnClose: Detach delayed for $timeout sec for user_id = {$user->getId()}...");
            $timer = $loop->addTimer($timeout, $this->detacher($user, $logger));
            $user->setDisconnectTimer($timer);
        } else {
            $logger->info("OnClose: Detached instantly user_id {$user->getId()}, connId = {$user->getConnectionId()}...");
	        $this->detacher($user, $logger);
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

	private function detacher(User $user, Logger $logger)
	{
		return function () use ($user, $logger) {
			$clients = DI::get()->getUsers();
			$clients->detach($user);
			$logger->info(
				"OnClose closure:
                closed userId = {$user->getId()},
                connId = {$user->getConnectionId()}
                Total user count {$clients->getTotalCount()}\n",
				[__CLASS__]
			);

			$this->notifyOnClose($user, $clients);
			$this->cleanPendingQueue($user);

			ChannelsCollection::get()->clean($user);

			$props = $user->getProperties();
			$props->setOnlineCount(time() - $user->getLoginTime() + $props->getOnlineCount());

			$online = OnlineDAO::create();
			$online->setOnline(
				$user->getChannelId(),
				$clients->getClientsCount($user->getChannelId())
			);
			$online->dropOne($user->getChannelId(), $user->getId());

			$user->save();

			//update access time
			$sessionHandler = DI::get()->getSession();
			$sessionHandler->store($user->getToken(), $user->getId());
			unset($clients);
			unset($sessionHandler);
			unset($user);
		};
	}
}
