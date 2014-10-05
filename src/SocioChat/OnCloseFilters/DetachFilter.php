<?php

namespace SocioChat\OnCloseFilters;

use SocioChat\DI;
use SocioChat\Chain\ChainContainer;
use SocioChat\Chain\ChainInterface;
use SocioChat\Clients\ChannelsCollection;
use SocioChat\Clients\PendingDuals;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Controllers\Helpers\ChannelNotifier;
use SocioChat\Message\MsgToken;
use SocioChat\Response\MessageResponse;
use SocioChat\Session\DBSessionHandler;


class DetachFilter implements ChainInterface
{
    public function handleRequest(ChainContainer $chain)
    {
        $user = $chain->getFrom();
        $loop = DI::get()->container()->get('eventloop');
        $logger = DI::get()->getLogger();

        $detacher = function () use ($user, $logger) {
            $clients = DI::get()->getUsers();
            $clients->detach($user);
            $logger->info("OnClose closure: closed userId = {$user->getId()}, connId = {$user->getConnectionId()}\nTotal user count {$clients->getTotalCount()}\n",
                [__CLASS__]);

            $this->notifyOnClose($user, $clients);
            $this->cleanPendingQueue($user);

            ChannelsCollection::get()->clean($user);

            $user->save();
            //update access time
            $sessionHandler = new DBSessionHandler();
            $sessionHandler->store($user->getToken(), $user->getId());
            unset($clients);
            unset($sessionHandler);
            unset($user);
        };

        if ($user->isAsyncDetach()) {
            $timeout = DI::get()->getConfig()->session->timeout;
            $logger->info("OnClose: Detach delayed for $timeout sec for user_id = {$user->getId()}...", [__CLASS__]);
            $timer = $loop->addTimer($timeout, $detacher);
            $user->setDisconnectTimer($timer);
        } else {
            $logger->info("OnClose: Detached instantly user_id {$user->getId()}, connId = {$user->getConnectionId()}...",
                [__CLASS__]);
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