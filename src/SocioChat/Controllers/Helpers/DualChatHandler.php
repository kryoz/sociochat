<?php

namespace SocioChat\Controllers\Helpers;

use SocioChat\Application\Chain\ChainContainer;
use SocioChat\Clients\ChannelsCollection;
use SocioChat\Clients\PendingDuals;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\DI;
use SocioChat\Enum\TimEnum;
use SocioChat\Message\MsgContainer;
use SocioChat\Message\MsgToken;
use SocioChat\Response\MessageResponse;

class DualChatHandler
{
    public static function run(ChainContainer $chain)
    {
        $duals = PendingDuals::get();
        $users = DI::get()->getUsers();
        $user = $chain->getFrom();
        $lang = $user->getLang();

        if ($user->getProperties()->getTim()->getId() == TimEnum::ANY) {
            $user->send(['msg' => $lang->getPhrase('SelectTIMinProfile')]);
            return;
        }

        if ($user->isInPrivateChat()) {
            $user->send(['msg' => $lang->getPhrase('ThisFunctionWorkInPublicOnly')]);
            return;
        }

        if ($duals->getUserPosition($user)) {
            $user->send(['msg' => $lang->getPhrase('YouAlreadySentRequestOnSearch')]);
            return;
        }

        if ($dualUserId = $duals->matchDual($user)) {
            $dualUser = $users->getClientById($dualUserId);
            $oldChatId = $user->getChannelId();
            $newChatRoomId = uniqid('_', 1);

            ChannelsCollection::get()->createChannel($newChatRoomId);
            $dualUser->setChannelId($newChatRoomId);
            $dualUser->save();

            $user->setChannelId($newChatRoomId);
            $user->save();

            self::sendMatchResponse($users->getUsersByChatId($newChatRoomId), MsgToken::create('DualIsFound'));
            self::renewGuestsList($oldChatId, MsgToken::create('DualizationStarted'));
            self::sendRenewPositions($duals->getUsersByDual($user));
            return;
        }

        self::sendPendingResponse($user, MsgToken::create('DualPending'), true);
        self::dualGuestsList($user);
    }

    private static function renewGuestsList($oldChatId, MsgContainer $msg)
    {
        $allUsers = DI::get()->getUsers();
        $newCommonList = $allUsers->getUsersByChatId($oldChatId);
        $response = (new MessageResponse())
            ->setTime(null)
            ->setChannelId($oldChatId)
            ->setMsg($msg)
            ->setGuests($newCommonList);

        $allUsers
            ->setResponse($response)
            ->notify();
    }

    private static function dualGuestsList(User $user)
    {
        $dualUsers = DI::get()->getUsers()->getUsersByChatId($user->getChannelId());
        $dual = TimEnum::create(PendingDuals::get()->getDualTim($user->getProperties()->getTim()));

        foreach ($dualUsers as $n => $partner) {
            $props = $partner->getProperties();
            if ($props->getTim()->getId() != $dual->getId() && $props->getTim()->getId() != TimEnum::ANY) {
                unset($dualUsers[$n]);
            }
            if ($props->getSex()->getId() == $user->getProperties()->getSex()->getId()) {
                unset($dualUsers[$n]);
            }
        }

        if (empty($dualUsers)) {
            return;
        }

        $collection = new UserCollection();
        foreach ($dualUsers as $partner) {
            $collection->attach($partner);
        }

        $response = (new MessageResponse())
            ->setTime(null)
            ->setChannelId($user->getChannelId())
            ->setMsg(MsgToken::create('DualIsWanted', $dual->getShortName()));

        $collection
            ->setResponse($response)
            ->notify(false);
    }

    private static function sendRenewPositions(array $userIds)
    {
        if (empty($userIds)) {
            return;
        }

        $notification = new UserCollection();
        $users = DI::get()->getUsers();

        foreach ($userIds as $userId) {
            $user = $users->getClientById($userId);
            $response = (new MessageResponse())
                ->setMsg(MsgToken::create('DualQueueShifted', count($userIds)))
                ->setDualChat('init')
                ->setTime(null)
                ->setChannelId($user->getChannelId());

            $notification
                ->attach($user)
                ->setResponse($response);
        }

        $notification->notify(false);
    }

    private static function sendPendingResponse(User $user, MsgContainer $msg)
    {
        $response = (new MessageResponse())
            ->setMsg($msg)
            ->setTime(null)
            ->setChannelId($user->getChannelId())
            ->setDualChat('init');

        (new UserCollection())
            ->attach($user)
            ->setResponse($response)
            ->notify(false);
    }

    private static function sendMatchResponse(array $users, MsgContainer $msg)
    {
        $notification = new UserCollection();

        foreach ($users as $user) {
            $notification->attach($user);
        }

        /* @var $user User */
        $user = $users[0];

        $response = (new MessageResponse())
            ->setDualChat('match')
            ->setMsg($msg)
            ->setChannelId($user->getChannelId())
            ->setGuests(DI::get()->getUsers()->getUsersByChatId($user->getChannelId()));

        $notification
            ->setResponse($response)
            ->notify();
    }
}
