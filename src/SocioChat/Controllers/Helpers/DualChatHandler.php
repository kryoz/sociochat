<?php

namespace SocioChat\Controllers\Helpers;


use SocioChat\Chain\ChainContainer;
use SocioChat\Clients\PendingDuals;
use SocioChat\Clients\User;
use SocioChat\Clients\UserCollection;
use SocioChat\Enum\TimEnum;
use SocioChat\Message\MsgContainer;
use SocioChat\Message\MsgToken;
use SocioChat\Response\MessageResponse;

class DualChatHandler
{
	public static function run(ChainContainer $chain)
	{
		$duals = PendingDuals::get();
		$users = UserCollection::get();
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

		$tim = $user->getProperties()->getTim();

		if ($dualUserId = $duals->matchDual($user)) {
			$dualUser = $users->getClientById($dualUserId);
			$oldChatId = $user->getChatId();
			$newChatRoomId = uniqid('_', 1);

			$dualUser->setChatId($newChatRoomId);
			$dualUser->save();

			$user->setChatId($newChatRoomId);
			$user->save();

			self::sendMatchResponse($users->getUsersByChatId($newChatRoomId), MsgToken::create('DualIsFound'));
			self::renewGuestsList($oldChatId, MsgToken::create('DualizationStarted'));
			self::sendRenewPositions($duals->getUsersByDualTim($tim));
			return;
		}

		self::sendPendingResponse($user, MsgToken::create('DualPending'), true);
		self::dualGuestsList($user);
	}

	private static function renewGuestsList($oldChatId, MsgContainer $msg)
	{
		$allUsers = UserCollection::get();
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
		$dualUsers = UserCollection::get()->getUsersByChatId($user->getChatId());
		if (!$dual = TimEnum::create(PendingDuals::get()->getDualTim($user->getProperties()->getTim()))) {
			return;
		}

		foreach ($dualUsers as $n => $guest) {
			if ($guest->getProperties()->getTim()->getId() != $dual->getId() && $guest->getProperties()->getTim()->getId() != TimEnum::ANY) {
				unset($dualUsers[$n]);
			}
		}

		if (empty($dualUsers)) {
			return;
		}

		$collection = new UserCollection();
		foreach ($dualUsers as $guest) {
			$collection->attach($guest);
		}

		$response = (new MessageResponse())
			->setTime(null)
			->setChannelId($user->getChatId())
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
		$users = UserCollection::get();

		foreach ($userIds as $userId) {
			$user = $users->getClientById($userId);
			$response = (new MessageResponse())
				->setMsg(MsgToken::create('DualQueueShifted', count($userIds)))
				->setDualChat('init')
				->setTime(null)
				->setChannelId($user->getChatId());

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
			->setChannelId($user->getChatId())
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
			->setChannelId($user->getChatId())
			->setGuests(UserCollection::get()->getUsersByChatId($user->getChatId()));

		$notification
			->setResponse($response)
			->notify();
	}
} 